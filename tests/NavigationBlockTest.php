<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;

final class NavigationBlockTest extends TestCase {

	protected function setUp(): void {
		Fixtures::reset();
	}

	public function test_find_alternate_href_returns_null_for_an_empty_target(): void {
		$this->assertNull( sevmatic_hreflang_find_alternate_href( array(), '' ) );
	}

	public function test_find_alternate_href_returns_null_when_no_alternate_matches(): void {
		$alternates = array(
			array( 'hreflang' => 'fr-FR', 'href' => 'https://example.com/fr/' ),
		);

		$this->assertNull( sevmatic_hreflang_find_alternate_href( $alternates, 'en-US' ) );
	}

	public function test_find_alternate_href_matches_case_insensitively(): void {
		$alternates = array(
			array( 'hreflang' => 'EN-us', 'href' => 'https://example.com/en/' ),
		);

		$this->assertSame( 'https://example.com/en/', sevmatic_hreflang_find_alternate_href( $alternates, 'en-US' ) );
	}

	public function test_find_alternate_href_skips_incomplete_rows(): void {
		$alternates = array(
			array( 'hreflang' => 'en-US', 'href' => '' ),
			array( 'hreflang' => '', 'href' => 'https://example.com/en/' ),
			array( 'hreflang' => 'en-US', 'href' => 'https://example.com/en-complete/' ),
		);

		$this->assertSame( 'https://example.com/en-complete/', sevmatic_hreflang_find_alternate_href( $alternates, 'en-US' ) );
	}

	public function test_render_language_link_is_empty_without_a_configured_target(): void {
		Fixtures::$isSingular = true;
		Fixtures::$queriedPostType = 'page';
		Fixtures::$queriedObjectId = 42;
		Fixtures::$permalinks[42] = 'https://example.com/imprint/';

		$this->assertSame( '', sevmatic_hreflang_render_language_link( array( 'hreflang' => '' ) ) );
	}

	public function test_render_language_link_is_empty_outside_a_supported_view(): void {
		$this->assertSame( '', sevmatic_hreflang_render_language_link( array( 'hreflang' => 'en-US' ) ) );
	}

	public function test_render_language_link_is_empty_without_a_matching_alternate(): void {
		Fixtures::$isSingular = true;
		Fixtures::$queriedPostType = 'page';
		Fixtures::$queriedObjectId = 42;
		Fixtures::$permalinks[42] = 'https://example.com/imprint/';
		Fixtures::$postMeta[42]['hreflang_alternates'] = array(
			array( 'hreflang' => 'fr-FR', 'href' => 'https://example.com/fr/imprint/' ),
		);

		$this->assertSame( '', sevmatic_hreflang_render_language_link( array( 'hreflang' => 'en-US' ) ) );
	}

	public function test_render_language_link_renders_the_matching_alternate(): void {
		Fixtures::$isSingular = true;
		Fixtures::$queriedPostType = 'page';
		Fixtures::$queriedObjectId = 42;
		Fixtures::$permalinks[42] = 'https://example.com/imprint/';
		Fixtures::$postMeta[42]['hreflang_alternates'] = array(
			array( 'hreflang' => 'en-US', 'href' => 'https://example.com/en/imprint/' ),
		);

		$output = sevmatic_hreflang_render_language_link( array( 'hreflang' => 'en-US', 'label' => 'English' ) );

		$this->assertSame(
			'<li class="wp-block-navigation-item"><a class="wp-block-navigation-item__content" href="https://example.com/en/imprint/" hreflang="en-US"><span class="wp-block-navigation-item__label">English</span></a></li>',
			$output
		);
	}

	public function test_render_language_link_includes_the_icon_when_set(): void {
		Fixtures::$isSingular = true;
		Fixtures::$queriedPostType = 'page';
		Fixtures::$queriedObjectId = 42;
		Fixtures::$permalinks[42] = 'https://example.com/imprint/';
		Fixtures::$postMeta[42]['hreflang_alternates'] = array(
			array( 'hreflang' => 'en-US', 'href' => 'https://example.com/en/imprint/' ),
		);

		$output = sevmatic_hreflang_render_language_link( array( 'hreflang' => 'en-US', 'label' => 'English', 'icon' => '🇬🇧' ) );

		$this->assertSame(
			'<li class="wp-block-navigation-item"><a class="wp-block-navigation-item__content" href="https://example.com/en/imprint/" hreflang="en-US"><span class="wp-block-navigation-item__icon">🇬🇧</span><span class="wp-block-navigation-item__label">English</span></a></li>',
			$output
		);
	}

	public function test_render_language_link_omits_the_icon_span_when_not_set(): void {
		Fixtures::$isSingular = true;
		Fixtures::$queriedPostType = 'page';
		Fixtures::$queriedObjectId = 42;
		Fixtures::$permalinks[42] = 'https://example.com/imprint/';
		Fixtures::$postMeta[42]['hreflang_alternates'] = array(
			array( 'hreflang' => 'en-US', 'href' => 'https://example.com/en/imprint/' ),
		);

		$output = sevmatic_hreflang_render_language_link( array( 'hreflang' => 'en-US', 'label' => 'English' ) );

		$this->assertStringNotContainsString( 'wp-block-navigation-item__icon', $output );
	}

	public function test_render_language_link_includes_the_tooltip_when_set(): void {
		Fixtures::$isSingular = true;
		Fixtures::$queriedPostType = 'page';
		Fixtures::$queriedObjectId = 42;
		Fixtures::$permalinks[42] = 'https://example.com/imprint/';
		Fixtures::$postMeta[42]['hreflang_alternates'] = array(
			array( 'hreflang' => 'en-US', 'href' => 'https://example.com/en/imprint/' ),
		);

		$output = sevmatic_hreflang_render_language_link(
			array(
				'hreflang' => 'en-US',
				'label'    => 'English',
				'tooltip'  => 'Go to the English version of this page.',
			)
		);

		$this->assertSame(
			'<li class="wp-block-navigation-item"><a class="wp-block-navigation-item__content" href="https://example.com/en/imprint/" hreflang="en-US" title="Go to the English version of this page."><span class="wp-block-navigation-item__label">English</span></a></li>',
			$output
		);
	}

	public function test_render_language_link_omits_the_title_attribute_when_no_tooltip_is_set(): void {
		Fixtures::$isSingular = true;
		Fixtures::$queriedPostType = 'page';
		Fixtures::$queriedObjectId = 42;
		Fixtures::$permalinks[42] = 'https://example.com/imprint/';
		Fixtures::$postMeta[42]['hreflang_alternates'] = array(
			array( 'hreflang' => 'en-US', 'href' => 'https://example.com/en/imprint/' ),
		);

		$output = sevmatic_hreflang_render_language_link( array( 'hreflang' => 'en-US', 'label' => 'English' ) );

		$this->assertStringNotContainsString( 'title=', $output );
	}

	public function test_render_language_link_falls_back_to_the_hreflang_code_as_the_label(): void {
		Fixtures::$isSingular = true;
		Fixtures::$queriedPostType = 'page';
		Fixtures::$queriedObjectId = 42;
		Fixtures::$permalinks[42] = 'https://example.com/imprint/';
		Fixtures::$postMeta[42]['hreflang_alternates'] = array(
			array( 'hreflang' => 'en-US', 'href' => 'https://example.com/en/imprint/' ),
		);

		$output = sevmatic_hreflang_render_language_link( array( 'hreflang' => 'en-US' ) );

		$this->assertStringContainsString( '<span class="wp-block-navigation-item__label">en-US</span>', $output );
	}
}
