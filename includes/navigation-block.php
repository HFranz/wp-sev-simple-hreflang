<?php
/**
 * Registers a "Language Link" block that can be added inside the block-theme
 * Navigation block. It renders a menu item linking to the current page's
 * hreflang alternate for a chosen language — and renders nothing at all
 * (no <li>, not just hidden) when the current page has no such alternate.
 *
 * @package sevmatic
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		wp_register_script(
			'sev-simple-hreflang-language-link',
			SEV_SIMPLE_HREFLANG_URL . 'blocks/language-link/index.js',
			array( 'wp-blocks', 'wp-block-editor', 'wp-element', 'wp-components', 'wp-i18n', 'wp-hooks' ),
			SEV_SIMPLE_HREFLANG_VERSION,
			true
		);

		wp_set_script_translations( 'sev-simple-hreflang-language-link', 'sev-simple-hreflang', SEV_SIMPLE_HREFLANG_PATH . 'languages' );

		register_block_type(
			SEV_SIMPLE_HREFLANG_PATH . 'blocks/language-link',
			array(
				'render_callback' => 'sevmatic_hreflang_render_language_link',
			)
		);
	}
);

add_action(
	'enqueue_block_editor_assets',
	function () {
		wp_enqueue_script( 'sev-simple-hreflang-language-link' );
	}
);

/**
 * Finds the href of the alternate matching a given hreflang code.
 *
 * @param array  $alternates      List of ['hreflang' => string, 'href' => string].
 * @param string $target_hreflang The hreflang code to look for, e.g. "en-US".
 */
function sevmatic_hreflang_find_alternate_href( array $alternates, string $target_hreflang ): ?string {
	if ( '' === $target_hreflang ) {
		return null;
	}

	foreach ( $alternates as $alternate ) {
		if ( empty( $alternate['hreflang'] ) || empty( $alternate['href'] ) ) {
			continue;
		}

		if ( 0 === strcasecmp( $alternate['hreflang'], $target_hreflang ) ) {
			return $alternate['href'];
		}
	}

	return null;
}

/**
 * Renders the "Language Link" block. Returns an empty string — so no <li>
 * is added to the navigation markup — when the current page has no hreflang
 * alternate matching the block's configured target language.
 *
 * @param array $attributes Block attributes.
 */
function sevmatic_hreflang_render_language_link( array $attributes ): string {
	$target_hreflang = isset( $attributes['hreflang'] ) ? trim( (string) $attributes['hreflang'] ) : '';

	if ( '' === $target_hreflang ) {
		return '';
	}

	$context = sevmatic_hreflang_get_current_context();

	if ( null === $context ) {
		return '';
	}

	$href = sevmatic_hreflang_find_alternate_href( $context['alternates'], $target_hreflang );

	if ( null === $href ) {
		return '';
	}

	$label = isset( $attributes['label'] ) ? trim( (string) $attributes['label'] ) : '';

	if ( '' === $label ) {
		$label = $target_hreflang;
	}

	$icon      = isset( $attributes['icon'] ) ? trim( (string) $attributes['icon'] ) : '';
	$icon_html = '' === $icon ? '' : sprintf( '<span class="wp-block-navigation-item__icon">%s</span>', esc_html( $icon ) );

	$tooltip      = isset( $attributes['tooltip'] ) ? trim( (string) $attributes['tooltip'] ) : '';
	$tooltip_html = '' === $tooltip ? '' : sprintf( ' title="%s"', esc_attr( $tooltip ) );

	$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'wp-block-navigation-item' ) );

	return sprintf(
		'<li %1$s><a class="wp-block-navigation-item__content" href="%2$s" hreflang="%3$s"%6$s>%4$s<span class="wp-block-navigation-item__label">%5$s</span></a></li>',
		$wrapper_attributes,
		esc_url( $href ),
		esc_attr( $target_hreflang ),
		$icon_html,
		esc_html( $label ),
		$tooltip_html
	);
}
