<?php
/**
 * Plugin Name:       Content Guard - Copy Protection & Advertisement Popup
 * Plugin URI:        https://beplusthemes.com/content-guard/
 * Description:       Protect your content from copying, right-click, and DevTools access. Show advertisement popups (banner, AdSense, subscribe form, video, HTML) when violations are detected.
 * Version:           1.0.2
 * Author:            Beplus
 * Author URI:        https://beplusthemes.com/
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       content-guard
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Tested up to:      6.9
 *
 * @package ContentGuard
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'COGU_VERSION',    '1.0.2' );
define( 'COGU_FILE',       __FILE__ );
define( 'COGU_DIR',        plugin_dir_path( __FILE__ ) );
define( 'COGU_URL',        plugin_dir_url( __FILE__ ) );
define( 'COGU_BASENAME',   plugin_basename( __FILE__ ) );
define( 'COGU_OPTION_KEY', 'cogu_settings' );

// Autoload classes (includes/ for core; admin/ for admin-only classes).
spl_autoload_register( function ( string $class ): void {
    $prefix = 'COGU_';
    if ( strpos( $class, $prefix ) !== 0 ) {
        return;
    }
    $name = strtolower( str_replace( [ $prefix, '_' ], [ '', '-' ], $class ) );
    $candidates = [
        COGU_DIR . 'includes/class-' . $name . '.php',
        COGU_DIR . 'admin/class-' . $name . '.php',
    ];
    foreach ( $candidates as $file ) {
        if ( file_exists( $file ) ) {
            require_once $file;
            return;
        }
    }
} );

/**
 * Returns the singleton plugin instance.
 */
function cogu(): COGU_Plugin {
    return COGU_Plugin::instance();
}

// Boot.
add_action( 'plugins_loaded', 'cogu' );

// Activation / deactivation hooks.
register_activation_hook( COGU_FILE, [ 'COGU_Installer', 'activate' ] );
register_deactivation_hook( COGU_FILE, [ 'COGU_Installer', 'deactivate' ] );
