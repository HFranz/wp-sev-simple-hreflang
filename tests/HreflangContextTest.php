<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;

final class HreflangContextTest extends TestCase {

	protected function setUp(): void {
		Fixtures::reset();
	}

	public function test_returns_null_when_none_of_the_supported_views_match(): void {
		$this->assertNull( sevmatic_hreflang_get_current_context() );
	}

	public function test_resolves_url_and_alternates_for_a_singular_page(): void {
		Fixtures::$isSingular = true;
		Fixtures::$queriedPostType = 'page';
		Fixtures::$queriedObjectId = 42;
		Fixtures::$permalinks[42] = 'https://example.com/imprint/';
		Fixtures::$postMeta[42]['hreflang_alternates'] = array(
			array(
				'hreflang' => 'en-US',
				'href'     => 'https://example.com/en/imprint/',
			),
		);

		$context = sevmatic_hreflang_get_current_context();

		$this->assertSame( 'https://example.com/imprint/', $context['url'] );
		$this->assertSame(
			array(
				array(
					'hreflang' => 'en-US',
					'href'     => 'https://example.com/en/imprint/',
				),
			),
			$context['alternates']
		);
	}

	public function test_resolves_the_static_posts_page_via_page_for_posts_option(): void {
		Fixtures::$isHome = true;
		Fixtures::$isFrontPage = false;
		Fixtures::$options['page_for_posts'] = 7;
		Fixtures::$permalinks[7] = 'https://example.com/blog/';
		Fixtures::$postMeta[7]['hreflang_alternates'] = array(
			array(
				'hreflang' => 'fr-FR',
				'href'     => 'https://example.com/fr/blog/',
			),
		);

		$context = sevmatic_hreflang_get_current_context();

		$this->assertSame( 'https://example.com/blog/', $context['url'] );
		$this->assertSame( 'fr-FR', $context['alternates'][0]['hreflang'] );
	}

	public function test_posts_page_without_page_for_posts_option_returns_null(): void {
		Fixtures::$isHome = true;
		Fixtures::$isFrontPage = false;
		// No 'page_for_posts' option set (e.g. "Your homepage displays: Your latest posts").

		$this->assertNull( sevmatic_hreflang_get_current_context() );
	}

	public function test_home_that_is_also_the_front_page_is_not_treated_as_the_posts_page(): void {
		Fixtures::$isHome = true;
		Fixtures::$isFrontPage = true;
		Fixtures::$options['page_for_posts'] = 7;
		Fixtures::$permalinks[7] = 'https://example.com/blog/';

		$this->assertNull( sevmatic_hreflang_get_current_context() );
	}

	public function test_resolves_url_and_alternates_for_a_category_archive(): void {
		Fixtures::$isCategory = true;
		Fixtures::$queriedObjectId = 5;
		Fixtures::$termLinks[5] = 'https://example.com/category/news/';
		Fixtures::$termMeta[5]['hreflang_alternates'] = array(
			array(
				'hreflang' => 'de-DE',
				'href'     => 'https://example.com/de/category/news/',
			),
		);

		$context = sevmatic_hreflang_get_current_context();

		$this->assertSame( 'https://example.com/category/news/', $context['url'] );
		$this->assertSame( 'de-DE', $context['alternates'][0]['hreflang'] );
	}

	public function test_category_archive_with_broken_term_link_returns_null(): void {
		Fixtures::$isCategory = true;
		Fixtures::$queriedObjectId = 5;
		// No entry in Fixtures::$termLinks -> get_term_link() stub returns a WP_Error.

		$this->assertNull( sevmatic_hreflang_get_current_context() );
	}

	public function test_alternates_default_to_an_empty_array_when_nothing_is_stored(): void {
		Fixtures::$isSingular = true;
		Fixtures::$queriedPostType = 'post';
		Fixtures::$queriedObjectId = 1;
		Fixtures::$permalinks[1] = 'https://example.com/hello-world/';
		// No post meta stored for post 1.

		$context = sevmatic_hreflang_get_current_context();

		$this->assertSame( array(), $context['alternates'] );
	}
}
