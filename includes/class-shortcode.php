<?php
/**
 * [cogu_lock] shortcode.
 *
 * Usage:
 *   [cogu_lock]Protected content.[/cogu_lock]
 *   [cogu_lock require_login="1" message="Log in to view this."]Content.[/cogu_lock]
 *
 * @package WPContentGuard
 */

defined( 'ABSPATH' ) || exit;

final class COGU_Shortcode {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    private function __construct() {}

    private function init(): void {
        add_shortcode( 'cogu_lock', [ $this, 'render' ] );
    }

    /**
     * @param array|string $atts
     * @param string|null  $content
     */
    public function render( array|string $atts, ?string $content = null ): string {
        $atts = shortcode_atts(
            [
                'require_login' => '0',
                'message'       => '',
                'class'         => '',
            ],
            $atts,
            'cogu_lock'
        );

        // Server-side login gate.
        if ( '1' === $atts['require_login'] && ! is_user_logged_in() ) {
            $msg = $atts['message']
                ? sanitize_text_field( $atts['message'] )
                : esc_html__( 'Please log in to view this content.', 'content-guard' );
            return '<p class="cogu-login-required">' . esc_html( $msg ) . '</p>';
        }

        if ( null === $content || '' === $content ) {
            return '';
        }

        $extra   = sanitize_html_class( $atts['class'] );
        $classes = trim( 'cogu-locked ' . $extra );

        return '<div class="' . esc_attr( $classes ) . '">' . do_shortcode( $content ) . '</div>';
    }
}
