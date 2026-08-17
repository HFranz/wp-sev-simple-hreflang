<?php
/**
 * Removes all hreflang data stored by the plugin (post meta and term meta)
 * when the plugin is deleted.
 *
 * @package sevmatic
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

const SEV_SIMPLE_HREFLANG_UNINSTALL_META_KEY = 'hreflang_alternates';

/**
 * Deletes the hreflang post meta and term meta on the current site.
 */
function sev_simple_hreflang_uninstall_current_site(): void {
	global $wpdb;

	// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- uninstall routine, no WP API equivalent for "delete all meta with this key"; caching is irrelevant while uninstalling.
	$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => SEV_SIMPLE_HREFLANG_UNINSTALL_META_KEY ) );
	$wpdb->delete( $wpdb->termmeta, array( 'meta_key' => SEV_SIMPLE_HREFLANG_UNINSTALL_META_KEY ) );
	// phpcs:enable
}

/**
 * Cleans up the current site, or every site on a multisite network.
 */
function sev_simple_hreflang_uninstall(): void {
	if ( ! is_multisite() ) {
		sev_simple_hreflang_uninstall_current_site();
		return;
	}

	$site_ids = get_sites( array( 'fields' => 'ids' ) );

	foreach ( $site_ids as $site_id ) {
		switch_to_blog( $site_id );
		sev_simple_hreflang_uninstall_current_site();
		restore_current_blog();
	}
}
sev_simple_hreflang_uninstall();
