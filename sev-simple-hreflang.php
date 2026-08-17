<?php
/**
 * Plugin Name:       SEV Simple hreflang
 * Description:       Adds hreflang alternates for pages, posts, and categories and outputs them as <link rel="alternate"> in <head>, including a self-referencing link.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            sevmatic
 * Author URI:        https://sevmatic.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sev-simple-hreflang
 * Domain Path:       /languages
 *
 * @package sevmatic
 */

defined( 'ABSPATH' ) || exit;

define( 'SEV_SIMPLE_HREFLANG_VERSION', '1.0.0' );
define( 'SEV_SIMPLE_HREFLANG_FILE', __FILE__ );
define( 'SEV_SIMPLE_HREFLANG_URL', plugin_dir_url( __FILE__ ) );
define( 'SEV_SIMPLE_HREFLANG_PATH', plugin_dir_path( __FILE__ ) );

require_once SEV_SIMPLE_HREFLANG_PATH . 'includes/hreflang.php';
