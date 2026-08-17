<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;

final class WpHeadOutputTest extends TestCase {

	protected function setUp(): void {
		Fixtures::reset();
	}

	private function renderWpHead(): string {
		ob_start();
		do_action( 'wp_head' );
		return ob_get_clean();
	}

	public function test_outputs_nothing_outside_a_supported_view(): void {
		$this->assertSame( '', $this->renderWpHead() );
	}

	public function test_outputs_a_self_referencing_link_even_without_any_stored_alternates(): void {
		Fixtures::$isSingular = true;
		Fixtures::$queriedPostType = 'page';
		Fixtures::$queriedObjectId = 42;
		Fixtures::$permalinks[42] = 'https://example.com/imprint/';
		Fixtures::$bloginfo['language'] = 'de-DE';

		$output = $this->renderWpHead();

		$this->assertSame(
			'<link rel="alternate" hreflang="de-DE" href="https://example.com/imprint/" />' . "\n",
			$output
		);
	}

	public function test_outputs_the_self_link_followed_by_every_valid_alternate(): void {
		Fixtures::$isSingular = true;
		Fixtures::$queriedPostType = 'page';
		Fixtures::$queriedObjectId = 42;
		Fixtures::$permalinks[42] = 'https://example.com/imprint/';
		Fixtures::$bloginfo['language'] = 'de-DE';
		Fixtures::$postMeta[42]['hreflang_alternates'] = array(
			array( 'hreflang' => 'en-US', 'href' => 'https://example.com/en/imprint/' ),
			array( 'hreflang' => 'fr-FR', 'href' => 'https://example.com/fr/imprint/' ),
		);

		$output = $this->renderWpHead();

		$this->assertSame(
			'<link rel="alternate" hreflang="de-DE" href="https://example.com/imprint/" />' . "\n"
			. '<link rel="alternate" hreflang="en-US" href="https://example.com/en/imprint/" />' . "\n"
			. '<link rel="alternate" hreflang="fr-FR" href="https://example.com/fr/imprint/" />' . "\n",
			$output
		);
	}

	public function test_skips_alternates_missing_a_hreflang_or_href(): void {
		Fixtures::$isSingular = true;
		Fixtures::$queriedPostType = 'page';
		Fixtures::$queriedObjectId = 42;
		Fixtures::$permalinks[42] = 'https://example.com/imprint/';
		Fixtures::$bloginfo['language'] = 'de-DE';
		Fixtures::$postMeta[42]['hreflang_alternates'] = array(
			array( 'hreflang' => '', 'href' => 'https://example.com/incomplete-1/' ),
			array( 'hreflang' => 'en-US', 'href' => '' ),
			array( 'hreflang' => 'fr-FR', 'href' => 'https://example.com/fr/imprint/' ),
		);

		$output = $this->renderWpHead();

		$this->assertSame(
			'<link rel="alternate" hreflang="de-DE" href="https://example.com/imprint/" />' . "\n"
			. '<link rel="alternate" hreflang="fr-FR" href="https://example.com/fr/imprint/" />' . "\n",
			$output
		);
	}

	public function test_does_not_duplicate_the_self_link_when_an_editor_also_entered_it_as_an_alternate(): void {
		Fixtures::$isSingular = true;
		Fixtures::$queriedPostType = 'page';
		Fixtures::$queriedObjectId = 42;
		Fixtures::$permalinks[42] = 'https://example.com/imprint/';
		Fixtures::$bloginfo['language'] = 'de-DE';
		Fixtures::$postMeta[42]['hreflang_alternates'] = array(
			// Same language as get_bloginfo('language'), just different casing.
			array( 'hreflang' => 'DE-de', 'href' => 'https://example.com/imprint/' ),
			array( 'hreflang' => 'en-US', 'href' => 'https://example.com/en/imprint/' ),
		);

		$output = $this->renderWpHead();

		$this->assertSame(
			'<link rel="alternate" hreflang="de-DE" href="https://example.com/imprint/" />' . "\n"
			. '<link rel="alternate" hreflang="en-US" href="https://example.com/en/imprint/" />' . "\n",
			$output
		);
	}
}
