<?php /** @noinspection PhpUnused */

declare( strict_types=1 );

/*
 * Minimal WordPress stubs for PHPUnit tests without a full WP bootstrap.
 * Only what includes/hreflang.php actually calls is provided, following the
 * same approach as sev-instructor-cockpit-for-learndash/tests/bootstrap.php.
 */

define( 'ABSPATH', dirname( __DIR__, 3 ) . '/' );

// ---------------------------------------------------------------------------
// Simple action system (same shape includes/hreflang.php expects)
// ---------------------------------------------------------------------------

$GLOBALS['wp_filter'] = array();

function add_action( string $tag, callable $callback, int $priority = 10, int $accepted_args = 1 ): bool {
	$GLOBALS['wp_filter'][ $tag ][ $priority ][] = $callback;
	return true;
}

function do_action( string $tag, mixed ...$args ): void {
	if ( empty( $GLOBALS['wp_filter'][ $tag ] ) ) {
		return;
	}
	ksort( $GLOBALS['wp_filter'][ $tag ] );
	foreach ( $GLOBALS['wp_filter'][ $tag ] as $callbacks ) {
		foreach ( $callbacks as $callback ) {
			$callback( ...$args );
		}
	}
}

// ---------------------------------------------------------------------------
// WP core classes
// ---------------------------------------------------------------------------

class WP_Error {
	private string $message;

	public function __construct( string $code = '', string $message = '' ) {
		$this->message = $message;
	}

	public function get_error_message(): string {
		return $this->message;
	}
}

class WP_Term {
	public int $term_id;

	public function __construct( int $term_id ) {
		$this->term_id = $term_id;
	}
}

// ---------------------------------------------------------------------------
// WP function stubs
// ---------------------------------------------------------------------------

function register_post_meta( string $post_type, string $meta_key, array $args ): bool {
	Fixtures::$registeredPostMeta[ $post_type ][ $meta_key ] = $args;
	return true;
}

function wp_register_script( string $handle, string $src, array $deps = array(), $ver = false, bool $in_footer = false ): bool {
	Fixtures::$registeredScripts[ $handle ] = $src;
	return true;
}

function wp_set_script_translations( string $handle, string $domain = 'default', string $path = '' ): bool {
	return true;
}

function get_current_screen(): ?object {
	return Fixtures::$currentScreen;
}

function wp_enqueue_script( string $handle ): void {
	Fixtures::$enqueuedScripts[] = $handle;
}

function sanitize_key( string $key ): string {
	return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) );
}

function wp_unslash( mixed $value ): mixed {
	return $value;
}

function sanitize_text_field( string $value ): string {
	return trim( $value );
}

function esc_attr( string $value ): string {
	return htmlspecialchars( $value, ENT_QUOTES );
}

function esc_attr_e( string $text, string $domain = 'default' ): void {
	echo esc_attr( $text );
}

function esc_html_e( string $text, string $domain = 'default' ): void {
	echo htmlspecialchars( $text, ENT_QUOTES );
}

function esc_url( string $url ): string {
	return $url;
}

function esc_url_raw( string $url ): string {
	return $url;
}

function __( string $text, string $domain = 'default' ): string {
	return $text;
}

function wp_nonce_field( string $action = '-1', string $name = '_wpnonce' ): void {
	echo '<input type="hidden" name="' . esc_attr( $name ) . '" />';
}

function wp_verify_nonce( string $nonce, string $action = '-1' ): bool {
	return Fixtures::$nonceIsValid;
}

function current_user_can( string $cap ): bool {
	return Fixtures::$currentUserCapabilities[ $cap ] ?? false;
}

function get_term_meta( int $term_id, string $key, bool $single = false ): mixed {
	return Fixtures::$termMeta[ $term_id ][ $key ] ?? ( $single ? '' : array() );
}

function update_term_meta( int $term_id, string $key, mixed $value ): bool {
	Fixtures::$termMeta[ $term_id ][ $key ] = $value;
	return true;
}

function delete_term_meta( int $term_id, string $key ): bool {
	unset( Fixtures::$termMeta[ $term_id ][ $key ] );
	return true;
}

function get_post_meta( int $post_id, string $key, bool $single = false ): mixed {
	return Fixtures::$postMeta[ $post_id ][ $key ] ?? ( $single ? '' : array() );
}

function is_singular( array $post_types = array() ): bool {
	return Fixtures::$isSingular && in_array( Fixtures::$queriedPostType, $post_types, true );
}

function get_queried_object_id(): int {
	return Fixtures::$queriedObjectId;
}

function get_permalink( int $post_id ): string|false {
	return Fixtures::$permalinks[ $post_id ] ?? false;
}

function is_home(): bool {
	return Fixtures::$isHome;
}

function is_front_page(): bool {
	return Fixtures::$isFrontPage;
}

function is_category(): bool {
	return Fixtures::$isCategory;
}

function get_option( string $name, mixed $default = false ): mixed {
	return Fixtures::$options[ $name ] ?? $default;
}

function get_term_link( int $term_id, string $taxonomy = '' ): string|WP_Error {
	return Fixtures::$termLinks[ $term_id ] ?? new WP_Error( 'invalid_term', 'Invalid term.' );
}

function is_wp_error( mixed $thing ): bool {
	return $thing instanceof WP_Error;
}

function get_bloginfo( string $show = '' ): string {
	return Fixtures::$bloginfo[ $show ] ?? '';
}

// ---------------------------------------------------------------------------
// Configurable fixture store, reset before each test
// ---------------------------------------------------------------------------

class Fixtures {
	/** @var array<string, array<string, array<string, mixed>>> register_post_meta() call log. */
	public static array $registeredPostMeta = array();
	/** @var array<string, string> wp_register_script() call log, keyed by handle. */
	public static array $registeredScripts = array();
	/** @var string[] wp_enqueue_script() call log. */
	public static array $enqueuedScripts = array();
	public static ?object $currentScreen = null;

	public static bool $nonceIsValid = true;
	/** @var array<string, bool> current_user_can() store, keyed by capability. */
	public static array $currentUserCapabilities = array();

	/** @var array<int, array<string, mixed>> term meta store, keyed by term ID then meta key. */
	public static array $termMeta = array();
	/** @var array<int, array<string, mixed>> post meta store, keyed by post ID then meta key. */
	public static array $postMeta = array();

	public static bool $isSingular = false;
	public static string $queriedPostType = '';
	public static int $queriedObjectId = 0;
	/** @var array<int, string|false> get_permalink() store, keyed by post ID. */
	public static array $permalinks = array();
	public static bool $isHome = false;
	public static bool $isFrontPage = false;
	public static bool $isCategory = false;
	/** @var array<string, mixed> get_option() store. */
	public static array $options = array();
	/** @var array<int, string|WP_Error> get_term_link() store, keyed by term ID. */
	public static array $termLinks = array();
	/** @var array<string, string> get_bloginfo() store. */
	public static array $bloginfo = array();

	public static function reset(): void {
		self::$registeredPostMeta = array();
		self::$registeredScripts = array();
		self::$enqueuedScripts = array();
		self::$currentScreen = null;
		self::$nonceIsValid = true;
		self::$currentUserCapabilities = array();
		self::$termMeta = array();
		self::$postMeta = array();
		self::$isSingular = false;
		self::$queriedPostType = '';
		self::$queriedObjectId = 0;
		self::$permalinks = array();
		self::$isHome = false;
		self::$isFrontPage = false;
		self::$isCategory = false;
		self::$options = array();
		self::$termLinks = array();
		self::$bloginfo = array();
	}
}

// ---------------------------------------------------------------------------
// Load the plugin's include (registers add_action callbacks; register_*
// callbacks that would normally fire on 'init' are never triggered by these
// tests, which call the plugin's functions directly instead)
// ---------------------------------------------------------------------------

require_once dirname( __DIR__ ) . '/includes/hreflang.php';
