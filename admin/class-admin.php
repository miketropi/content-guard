<?php
/**
 * Admin controller — settings page, menu, save handler.
 *
 * @package WPContentGuard
 */

defined( 'ABSPATH' ) || exit;

final class COGU_Admin {

    private static ?self $instance = null;
    private string        $hook     = '';

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    private function __construct() {}

    private function init(): void {
        add_action( 'admin_menu',             [ $this, 'add_menu' ] );
        add_action( 'admin_enqueue_scripts',  [ $this, 'enqueue_assets' ] );
        add_action( 'admin_post_cogu_save',   [ $this, 'handle_save' ] );
        // "Settings" link on plugins list.
        add_filter( 'plugin_action_links_' . COGU_BASENAME, [ $this, 'add_settings_link' ] );
    }

    // -------------------------------------------------------------------------
    // Menu
    // -------------------------------------------------------------------------

    public function add_menu(): void {
        $this->hook = add_options_page(
            esc_html__( 'Content Guard', 'content-guard' ),
            esc_html__( 'Content Guard', 'content-guard' ),
            'manage_options',
            'content-guard',
            [ $this, 'render_page' ]
        );
    }

    public function add_settings_link( array $links ): array {
        $url  = admin_url( 'options-general.php?page=content-guard' );
        $link = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'content-guard' ) . '</a>';
        array_unshift( $links, $link );
        return $links;
    }

    // -------------------------------------------------------------------------
    // Assets (only on our page)
    // -------------------------------------------------------------------------

    public function enqueue_assets( string $hook ): void {
        if ( $hook !== $this->hook ) {
            return;
        }

        wp_enqueue_media(); // Media uploader for banner image & video.

        wp_enqueue_style(
            'cogu-admin',
            COGU_URL . 'admin/css/cogu-admin.css',
            [ 'wp-color-picker' ],
            COGU_VERSION
        );

        wp_enqueue_script(
            'cogu-admin',
            COGU_URL . 'admin/js/cogu-admin.js',
            [ 'jquery', 'wp-color-picker', 'media-upload' ],
            COGU_VERSION,
            true
        );

        wp_localize_script( 'cogu-admin', 'WPCGAdmin', [
            'mediaTitle'  => __( 'Select Media', 'content-guard' ),
            'mediaButton' => __( 'Use this file', 'content-guard' ),
        ] );
    }

    // -------------------------------------------------------------------------
    // Save handler (admin-post)
    // -------------------------------------------------------------------------

    public function handle_save(): void {
        // 1. Capability check.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to do this.', 'content-guard' ), 403 );
        }

        // 2. Nonce verification.
        if ( ! isset( $_POST['cogu_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cogu_nonce'] ), 'cogu_save_settings' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'content-guard' ), 403 );
        }

        // 3. Sanitize and save.
        $raw   = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification -- verified above.
        $clean = COGU_Settings::sanitize( (array) $raw );
        COGU_Settings::save( $clean );

        // 4. Redirect back with success notice.
        wp_safe_redirect(
            add_query_arg(
                [ 'page' => 'content-guard', 'updated' => '1' ],
                admin_url( 'options-general.php' )
            )
        );
        exit;
    }

    // -------------------------------------------------------------------------
    // Settings page render
    // -------------------------------------------------------------------------

    public function render_page(): void {
        // Capability re-check.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to view this page.', 'content-guard' ) );
        }

        $s          = COGU_Settings::all();
        $post_types = get_post_types( [ 'public' => true ], 'objects' );
        $categories = get_categories( [ 'hide_empty' => false ] );
        $updated    = isset( $_GET['updated'] ) && '1' === sanitize_key( $_GET['updated'] ); // phpcs:ignore -- read-only display.

        // Load the view.
        require_once COGU_DIR . 'admin/views/page-settings.php';
    }
}
