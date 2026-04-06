<?php
/**
 * Plugin installer — handles activation and deactivation.
 *
 * @package WPContentGuard
 */

defined( 'ABSPATH' ) || exit;

final class COGU_Installer {

    /**
     * Fired on plugin activation.
     * Sets default options only on first run (won't overwrite existing).
     */
    public static function activate(): void {
        // Require WP/PHP minimums before activating.
        if ( version_compare( get_bloginfo( 'version' ), '6.0', '<' ) ) {
            deactivate_plugins( COGU_BASENAME );
            wp_die(
                esc_html__( 'Content Guard requires WordPress 6.0 or higher.', 'content-guard' ),
                esc_html__( 'Activation Error', 'content-guard' ),
                [ 'back_link' => true ]
            );
        }

        if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
            deactivate_plugins( COGU_BASENAME );
            wp_die(
                esc_html__( 'Content Guard requires PHP 8.0 or higher.', 'content-guard' ),
                esc_html__( 'Activation Error', 'content-guard' ),
                [ 'back_link' => true ]
            );
        }

        // Write defaults only if option doesn't exist yet.
        if ( false === get_option( COGU_OPTION_KEY ) ) {
            // Load Settings class if not autoloaded yet.
            if ( ! class_exists( 'COGU_Settings' ) ) {
                require_once COGU_DIR . 'includes/class-settings.php';
            }
            add_option( COGU_OPTION_KEY, COGU_Settings::defaults(), '', false );
        }

        flush_rewrite_rules();
    }

    /**
     * Fired on plugin deactivation — intentionally left minimal.
     * Options are NOT deleted here; deletion happens only in uninstall.php.
     */
    public static function deactivate(): void {
        flush_rewrite_rules();
    }
}
