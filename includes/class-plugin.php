<?php
/**
 * Main plugin class.
 *
 * @package WPContentGuard
 */

defined( 'ABSPATH' ) || exit;

final class COGU_Plugin {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->boot();
        }
        return self::$instance;
    }

    private function __construct() {}
    private function __clone() {}

    private function boot(): void {
        // i18n.
        add_action( 'init', [ $this, 'load_textdomain' ] );

        // Core modules — always loaded.
        COGU_Settings::instance();
        COGU_Protection::instance();
        COGU_Shortcode::instance();
        COGU_Frontend::instance();

        // Admin modules.
        if ( is_admin() ) {
            COGU_Admin::instance();
        }
    }

    public function load_textdomain(): void {
        
    }
}
