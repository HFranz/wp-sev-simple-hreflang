<?php
/**
 * Entfernt alle vom Plugin gespeicherten hreflang-Daten (Post-Meta und
 * Term-Meta) beim Löschen des Plugins.
 *
 * @package sevmatic
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

const SEV_SIMPLE_HREFLANG_UNINSTALL_META_KEY = 'hreflang_alternates';

/**
 * Löscht das hreflang-Post- und Term-Meta auf der aktuellen Site.
 */
function sev_simple_hreflang_uninstall_current_site(): void {
	global $wpdb;

	// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Uninstall-Routine, kein WP-API-Äquivalent für "alle Meta mit diesem Key löschen", Caching irrelevant beim Deinstallieren.
	$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => SEV_SIMPLE_HREFLANG_UNINSTALL_META_KEY ) );
	$wpdb->delete( $wpdb->termmeta, array( 'meta_key' => SEV_SIMPLE_HREFLANG_UNINSTALL_META_KEY ) );
	// phpcs:enable
}

/**
 * Räumt auf der aktuellen Site auf, bzw. auf allen Sites im Multisite-Netzwerk.
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
