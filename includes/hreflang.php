<?php
/**
 * Lets editors attach alternate-language versions to a page/post/category,
 * output as <link rel="alternate" hreflang="…"> in <head> (plus a
 * self-referencing link to the current page).
 *
 * Deliberately no content block: hreflang annotations are only evaluated by
 * search engines in <head> (or via HTTP header or sitemap), not in the body.
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
 * Classic repeater form for categories: the block editor doesn't exist on
 * edit-tags.php/term.php, so this uses term meta instead of post meta and a
 * small vanilla-JS script instead of the React sidebar panel.
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

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only, just decides whether to enqueue an asset.
		$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_key( wp_unslash( $_GET['taxonomy'] ) ) : 'category';

		if ( in_array( $pagenow, array( 'edit-tags.php', 'term.php' ), true ) && 'category' === $taxonomy ) {
			wp_enqueue_script( 'sev-simple-hreflang-term-repeater' );
		}
	}
);

/**
 * Renders the repeater form for an array of hreflang/URL rows. Used on both
 * the "Add New Category" and the edit-category form; only the container ID
 * suffix differs.
 *
 * @param array  $alternates List of ['hreflang' => string, 'href' => string].
 * @param string $id_suffix  Suffix for the container ID ("add" or "edit").
 */
function sevmatic_hreflang_render_term_fields( array $alternates, string $id_suffix ): void {
	?>
	<div id="sevmatic-hreflang-rows-<?php echo esc_attr( $id_suffix ); ?>">
		<?php foreach ( $alternates as $index => $alternate ) : ?>
			<div class="sevmatic-hreflang-row">
				<input type="text" name="sevmatic_hreflang[<?php echo esc_attr( $index ); ?>][hreflang]" placeholder="en-US" value="<?php echo esc_attr( $alternate['hreflang'] ?? '' ); ?>" />
				<input type="url" name="sevmatic_hreflang[<?php echo esc_attr( $index ); ?>][href]" placeholder="https://example.com/en/category/" value="<?php echo esc_attr( $alternate['href'] ?? '' ); ?>" />
				<button type="button" class="button sevmatic-hreflang-remove-row"><?php esc_html_e( 'Remove', 'sev-simple-hreflang' ); ?></button>
			</div>
		<?php endforeach; ?>
	</div>
	<button type="button" class="button sevmatic-hreflang-add-row" data-target="sevmatic-hreflang-rows-<?php echo esc_attr( $id_suffix ); ?>" data-remove-label="<?php esc_attr_e( 'Remove', 'sev-simple-hreflang' ); ?>">
		<?php esc_html_e( 'Add language version', 'sev-simple-hreflang' ); ?>
	</button>
	<p class="description">
		<?php esc_html_e( 'Output as <link rel="alternate" hreflang="…"> in the <head> of this category archive page.', 'sev-simple-hreflang' ); ?>
	</p>
	<?php
	wp_nonce_field( 'sevmatic_hreflang_term', 'sevmatic_hreflang_nonce' );
}

add_action(
	'category_add_form_fields',
	function () {
		?>
		<div class="form-field">
			<label><?php esc_html_e( 'hreflang (alternate language versions)', 'sev-simple-hreflang' ); ?></label>
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
			<th scope="row"><label><?php esc_html_e( 'hreflang (alternate language versions)', 'sev-simple-hreflang' ); ?></label></th>
			<td><?php sevmatic_hreflang_render_term_fields( $alternates, 'edit' ); ?></td>
		</tr>
		<?php
	}
);

/**
 * Saves the hreflang rows for a category. Runs both on create and on edit
 * (create_category/edited_category fire regardless of whether the request
 * came in classically or via AJAX).
 *
 * @param int $term_id The term ID of the saved category.
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
		? wp_unslash( $_POST['sevmatic_hreflang'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each row is sanitized individually below.
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
 * Resolves the URL and stored hreflang alternates for the currently
 * requested view, if it's one of the supported contexts (post/page, static
 * posts page, category archive). Returns null if none of those apply.
 */
function sevmatic_hreflang_get_current_context(): ?array {
	if ( is_singular( array( 'post', 'page' ) ) ) {
		$post_id    = get_queried_object_id();
		$url        = get_permalink( $post_id );
		$alternates = get_post_meta( $post_id, SEVMATIC_HREFLANG_META_KEY, true );
	} elseif ( is_home() && ! is_front_page() ) {
		// Static posts page (Settings > Reading > "Posts page"): rendered as
		// an archive, so is_singular() is false here even though the page
		// has its own ID with its own post meta.
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

		// Self-referencing hreflang: Google's guidelines recommend that a
		// page with hreflang annotations also list itself as an alternate.
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

			// Avoid a duplicate if the page was also entered as its own alternate by mistake.
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
	3 // Right after the feed links (feed_links() runs at priority 2), instead of behind rsd_link, wlwmanifest_link, etc. (priority 10).
);
