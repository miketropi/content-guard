<?php
/**
 * Uninstall – remove all plugin data when the plugin is deleted.
 *
 * This file is called automatically by WordPress when the plugin is deleted
 * via the admin "Delete" link. It is NOT called on deactivation.
 *
 * @package WPContentGuard
 */

// Safety check — this file must only be called by WordPress uninstall.
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Remove plugin settings from all sites in a multisite network.
global $wpdb;

if ( is_multisite() ) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
    foreach ( $blog_ids as $blog_id ) {
        switch_to_blog( (int) $blog_id );
        delete_option( 'cogu_settings' );
        restore_current_blog();
    }
} else {
    delete_option( 'cogu_settings' );
}
