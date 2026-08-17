<?php
/**
 * Erlaubt es, pro Seite/Beitrag/Kategorie alternative Sprachversionen zu
 * hinterlegen, die als <link rel="alternate" hreflang="…"> im <head>
 * ausgegeben werden (plus ein Self-Referencing-Link auf die aktuelle Seite).
 *
 * Bewusst kein Content-Block: hreflang-Angaben werden von Suchmaschinen nur
 * im <head> (bzw. HTTP-Header oder Sitemap) ausgewertet, nicht im Body.
 *
 * @package sevmatic
 */

defined( 'ABSPATH' ) || exit;

define( 'SEVMATIC_HREFLANG_META_KEY', 'hreflang_alternates' );

add_action(
	'init',
	function () {
		$schema = array(
			'type'  => 'array',
			'items' => array(
				'type'       => 'object',
				'properties' => array(
					'hreflang' => array( 'type' => 'string' ),
					'href'     => array( 'type' => 'string' ),
				),
			),
		);

		foreach ( array( 'post', 'page' ) as $post_type ) {
			register_post_meta(
				$post_type,
				SEVMATIC_HREFLANG_META_KEY,
				array(
					'type'          => 'array',
					'single'        => true,
					'default'       => array(),
					'show_in_rest'  => array( 'schema' => $schema ),
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}
);

add_action(
	'init',
	function () {
		wp_register_script(
			'sev-simple-hreflang-sidebar',
			SEV_SIMPLE_HREFLANG_URL . 'public/js/hreflang-sidebar.js',
			array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n' ),
			SEV_SIMPLE_HREFLANG_VERSION,
			true
		);

		wp_set_script_translations( 'sev-simple-hreflang-sidebar', 'sev-simple-hreflang', SEV_SIMPLE_HREFLANG_PATH . 'languages' );
	}
);

add_action(
	'enqueue_block_editor_assets',
	function () {
		$screen = get_current_screen();

		if ( $screen && in_array( $screen->post_type, array( 'post', 'page' ), true ) ) {
			wp_enqueue_script( 'sev-simple-hreflang-sidebar' );
		}
	}
);

/**
 * Klassisches Repeater-Formular für Kategorien: der Block-Editor existiert
 * auf edit-tags.php/term.php nicht, daher Term-Meta statt Post-Meta und ein
 * kleines Vanilla-JS-Script statt des React-Sidebar-Panels.
 */
add_action(
	'init',
	function () {
		wp_register_script(
			'sev-simple-hreflang-term-repeater',
			SEV_SIMPLE_HREFLANG_URL . 'public/js/hreflang-term-repeater.js',
			array(),
			SEV_SIMPLE_HREFLANG_VERSION,
			true
		);
	}
);

add_action(
	'admin_enqueue_scripts',
	function () {
		global $pagenow;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nur lesend, um über das Enqueuen eines Assets zu entscheiden.
		$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_key( wp_unslash( $_GET['taxonomy'] ) ) : 'category';

		if ( in_array( $pagenow, array( 'edit-tags.php', 'term.php' ), true ) && 'category' === $taxonomy ) {
			wp_enqueue_script( 'sev-simple-hreflang-term-repeater' );
		}
	}
);

/**
 * Gibt das Repeater-Formular für ein Feld-Array mit hreflang/URL-Zeilen aus.
 * Wird sowohl im "Neue Kategorie hinzufügen"- als auch im Bearbeiten-Formular
 * verwendet; nur der Container-ID-Suffix unterscheidet sich.
 *
 * @param array  $alternates Liste von ['hreflang' => string, 'href' => string].
 * @param string $id_suffix  Suffix für die Container-ID ("add" oder "edit").
 */
function sevmatic_hreflang_render_term_fields( array $alternates, string $id_suffix ): void {
	?>
	<div id="sevmatic-hreflang-rows-<?php echo esc_attr( $id_suffix ); ?>">
		<?php foreach ( $alternates as $index => $alternate ) : ?>
			<div class="sevmatic-hreflang-row">
				<input type="text" name="sevmatic_hreflang[<?php echo esc_attr( $index ); ?>][hreflang]" placeholder="en-US" value="<?php echo esc_attr( $alternate['hreflang'] ?? '' ); ?>" />
				<input type="url" name="sevmatic_hreflang[<?php echo esc_attr( $index ); ?>][href]" placeholder="https://example.com/en/category/" value="<?php echo esc_attr( $alternate['href'] ?? '' ); ?>" />
				<button type="button" class="button sevmatic-hreflang-remove-row"><?php esc_html_e( 'Entfernen', 'sev-simple-hreflang' ); ?></button>
			</div>
		<?php endforeach; ?>
	</div>
	<button type="button" class="button sevmatic-hreflang-add-row" data-target="sevmatic-hreflang-rows-<?php echo esc_attr( $id_suffix ); ?>" data-remove-label="<?php esc_attr_e( 'Entfernen', 'sev-simple-hreflang' ); ?>">
		<?php esc_html_e( 'Sprachversion hinzufügen', 'sev-simple-hreflang' ); ?>
	</button>
	<p class="description">
		<?php esc_html_e( 'Wird als <link rel="alternate" hreflang="…"> im <head> dieser Kategorie-Archivseite ausgegeben.', 'sev-simple-hreflang' ); ?>
	</p>
	<?php
	wp_nonce_field( 'sevmatic_hreflang_term', 'sevmatic_hreflang_nonce' );
}

add_action(
	'category_add_form_fields',
	function () {
		?>
		<div class="form-field">
			<label><?php esc_html_e( 'hreflang (alternative Sprachversionen)', 'sev-simple-hreflang' ); ?></label>
			<?php sevmatic_hreflang_render_term_fields( array(), 'add' ); ?>
		</div>
		<?php
	}
);

add_action(
	'category_edit_form_fields',
	function ( WP_Term $term ) {
		$alternates = get_term_meta( $term->term_id, SEVMATIC_HREFLANG_META_KEY, true );

		if ( ! is_array( $alternates ) ) {
			$alternates = array();
		}
		?>
		<tr class="form-field">
			<th scope="row"><label><?php esc_html_e( 'hreflang (alternative Sprachversionen)', 'sev-simple-hreflang' ); ?></label></th>
			<td><?php sevmatic_hreflang_render_term_fields( $alternates, 'edit' ); ?></td>
		</tr>
		<?php
	}
);

/**
 * Speichert die hreflang-Zeilen für eine Kategorie. Läuft sowohl beim
 * Anlegen als auch beim Bearbeiten (create_category/edited_category feuern
 * unabhängig davon, ob die Anfrage klassisch oder per AJAX kommt).
 *
 * @param int $term_id Die Term-ID der gespeicherten Kategorie.
 */
function sevmatic_hreflang_save_term_meta( int $term_id ): void {
	if ( ! isset( $_POST['sevmatic_hreflang_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sevmatic_hreflang_nonce'] ) ), 'sevmatic_hreflang_term' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}

	$raw_rows = isset( $_POST['sevmatic_hreflang'] ) && is_array( $_POST['sevmatic_hreflang'] )
		? wp_unslash( $_POST['sevmatic_hreflang'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- jede Zeile wird unten einzeln sanitized.
		: array();

	$alternates = array();

	foreach ( $raw_rows as $row ) {
		$hreflang = isset( $row['hreflang'] ) ? sanitize_text_field( $row['hreflang'] ) : '';
		$href     = isset( $row['href'] ) ? esc_url_raw( $row['href'] ) : '';

		if ( '' === $hreflang || '' === $href ) {
			continue;
		}

		$alternates[] = array(
			'hreflang' => $hreflang,
			'href'     => $href,
		);
	}

	if ( empty( $alternates ) ) {
		delete_term_meta( $term_id, SEVMATIC_HREFLANG_META_KEY );
	} else {
		update_term_meta( $term_id, SEVMATIC_HREFLANG_META_KEY, $alternates );
	}
}
add_action( 'create_category', 'sevmatic_hreflang_save_term_meta' );
add_action( 'edited_category', 'sevmatic_hreflang_save_term_meta' );

/**
 * Ermittelt URL und hinterlegte hreflang-Alternates für die aktuell
 * aufgerufene Ansicht, sofern eine der unterstützten Kontexte vorliegt
 * (Seite/Beitrag, statische Beiträgeseite, Kategorie-Archiv). Gibt null
 * zurück, wenn keiner dieser Kontexte zutrifft.
 */
function sevmatic_hreflang_get_current_context(): ?array {
	if ( is_singular( array( 'post', 'page' ) ) ) {
		$post_id    = get_queried_object_id();
		$url        = get_permalink( $post_id );
		$alternates = get_post_meta( $post_id, SEVMATIC_HREFLANG_META_KEY, true );
	} elseif ( is_home() && ! is_front_page() ) {
		// Statische Beiträgeseite (Einstellungen > Lesen > "Beiträgeseite"):
		// wird als Archiv gerendert, is_singular() ist hier false, obwohl
		// die Seite eine eigene ID mit eigenem Post-Meta hat.
		$post_id = (int) get_option( 'page_for_posts' );

		if ( ! $post_id ) {
			return null;
		}

		$url        = get_permalink( $post_id );
		$alternates = get_post_meta( $post_id, SEVMATIC_HREFLANG_META_KEY, true );
	} elseif ( is_category() ) {
		$term_id = get_queried_object_id();
		$url     = get_term_link( $term_id, 'category' );

		if ( is_wp_error( $url ) ) {
			return null;
		}

		$alternates = get_term_meta( $term_id, SEVMATIC_HREFLANG_META_KEY, true );
	} else {
		return null;
	}

	if ( ! $url ) {
		return null;
	}

	return array(
		'url'        => $url,
		'alternates' => is_array( $alternates ) ? $alternates : array(),
	);
}

add_action(
	'wp_head',
	function () {
		$context = sevmatic_hreflang_get_current_context();

		if ( null === $context ) {
			return;
		}

		// Self-Referencing-hreflang: laut Google-Richtlinie sollte eine Seite
		// mit hreflang-Angaben auch sich selbst als Alternate auflisten.
		$self_hreflang = get_bloginfo( 'language' );

		printf(
			'<link rel="alternate" hreflang="%s" href="%s" />' . "\n",
			esc_attr( $self_hreflang ),
			esc_url( $context['url'] )
		);

		foreach ( $context['alternates'] as $alternate ) {
			if ( empty( $alternate['hreflang'] ) || empty( $alternate['href'] ) ) {
				continue;
			}

			// Duplikat vermeiden, falls die Seite versehentlich auch sich
			// selbst als Alternate einträgt.
			if ( 0 === strcasecmp( $alternate['hreflang'], $self_hreflang ) ) {
				continue;
			}

			printf(
				'<link rel="alternate" hreflang="%s" href="%s" />' . "\n",
				esc_attr( $alternate['hreflang'] ),
				esc_url( $alternate['href'] )
			);
		}
	},
	3 // Direkt nach den Feed-Links (feed_links() läuft auf Priorität 2), statt hinter rsd_link, wlwmanifest_link & Co. (Priorität 10).
);
