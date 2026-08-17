<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;

final class SaveTermMetaTest extends TestCase {

	protected function setUp(): void {
		Fixtures::reset();
		$_POST = array();
	}

	protected function tearDown(): void {
		$_POST = array();
	}

	public function test_does_nothing_without_a_nonce(): void {
		Fixtures::$currentUserCapabilities['manage_categories'] = true;
		$_POST['sevmatic_hreflang'] = array( array( 'hreflang' => 'en-US', 'href' => 'https://example.com/en/' ) );
		// No 'sevmatic_hreflang_nonce' in $_POST.

		sevmatic_hreflang_save_term_meta( 5 );

		$this->assertSame( array(), Fixtures::$termMeta[5]['hreflang_alternates'] ?? array() );
	}

	public function test_does_nothing_when_the_nonce_is_invalid(): void {
		Fixtures::$currentUserCapabilities['manage_categories'] = true;
		Fixtures::$nonceIsValid = false;
		$_POST['sevmatic_hreflang_nonce'] = 'anything';
		$_POST['sevmatic_hreflang']       = array( array( 'hreflang' => 'en-US', 'href' => 'https://example.com/en/' ) );

		sevmatic_hreflang_save_term_meta( 5 );

		$this->assertArrayNotHasKey( 5, Fixtures::$termMeta );
	}

	public function test_does_nothing_without_the_manage_categories_capability(): void {
		Fixtures::$nonceIsValid = true;
		Fixtures::$currentUserCapabilities['manage_categories'] = false;
		$_POST['sevmatic_hreflang_nonce'] = 'anything';
		$_POST['sevmatic_hreflang']       = array( array( 'hreflang' => 'en-US', 'href' => 'https://example.com/en/' ) );

		sevmatic_hreflang_save_term_meta( 5 );

		$this->assertArrayNotHasKey( 5, Fixtures::$termMeta );
	}

	public function test_saves_valid_rows_and_drops_incomplete_ones(): void {
		Fixtures::$nonceIsValid = true;
		Fixtures::$currentUserCapabilities['manage_categories'] = true;
		$_POST['sevmatic_hreflang_nonce'] = 'anything';
		$_POST['sevmatic_hreflang']       = array(
			array( 'hreflang' => 'en-US', 'href' => 'https://example.com/en/' ),
			array( 'hreflang' => '', 'href' => 'https://example.com/missing-hreflang/' ),
			array( 'hreflang' => 'fr-FR', 'href' => '' ),
			array( 'hreflang' => 'de-DE', 'href' => 'https://example.com/de/' ),
		);

		sevmatic_hreflang_save_term_meta( 5 );

		$this->assertSame(
			array(
				array( 'hreflang' => 'en-US', 'href' => 'https://example.com/en/' ),
				array( 'hreflang' => 'de-DE', 'href' => 'https://example.com/de/' ),
			),
			Fixtures::$termMeta[5]['hreflang_alternates']
		);
	}

	public function test_deletes_the_meta_when_every_row_ends_up_empty(): void {
		Fixtures::$nonceIsValid = true;
		Fixtures::$currentUserCapabilities['manage_categories'] = true;
		Fixtures::$termMeta[5]['hreflang_alternates'] = array( array( 'hreflang' => 'en-US', 'href' => 'https://example.com/en/' ) );

		$_POST['sevmatic_hreflang_nonce'] = 'anything';
		$_POST['sevmatic_hreflang']       = array( array( 'hreflang' => '', 'href' => '' ) );

		sevmatic_hreflang_save_term_meta( 5 );

		$this->assertArrayNotHasKey( 'hreflang_alternates', Fixtures::$termMeta[5] );
	}

	public function test_deletes_the_meta_when_no_rows_were_submitted_at_all(): void {
		Fixtures::$nonceIsValid = true;
		Fixtures::$currentUserCapabilities['manage_categories'] = true;
		Fixtures::$termMeta[5]['hreflang_alternates'] = array( array( 'hreflang' => 'en-US', 'href' => 'https://example.com/en/' ) );

		$_POST['sevmatic_hreflang_nonce'] = 'anything';
		// No 'sevmatic_hreflang' key at all in $_POST.

		sevmatic_hreflang_save_term_meta( 5 );

		$this->assertArrayNotHasKey( 'hreflang_alternates', Fixtures::$termMeta[5] );
	}
}
