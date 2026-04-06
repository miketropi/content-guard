<?php
/**
 * Frontend: enqueue assets and render the popup markup.
 *
 * @package WPContentGuard
 */

defined( 'ABSPATH' ) || exit;

final class COGU_Frontend {

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
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
        add_action( 'wp_footer',          [ $this, 'render_popup' ] );

        // Subscribe form AJAX handler (nopriv = logged-out users can submit).
        add_action( 'wp_ajax_cogu_subscribe',        [ $this, 'handle_subscribe' ] );
        add_action( 'wp_ajax_nopriv_cogu_subscribe', [ $this, 'handle_subscribe' ] );
    }

    // -------------------------------------------------------------------------
    // Assets
    // -------------------------------------------------------------------------

    public function enqueue(): void {
        if ( ! COGU_Protection::instance()->should_protect() ) {
            return;
        }

        $s = COGU_Settings::all();

        wp_enqueue_style(
            'cogu-popup',
            COGU_URL . 'assets/css/cogu-popup.css',
            [],
            COGU_VERSION
        );

        wp_enqueue_script(
            'cogu-protection',
            COGU_URL . 'assets/js/cogu-protection.js',
            [],          // No jQuery dependency.
            COGU_VERSION,
            true         // Footer.
        );

        // Config passed to JS — only non-sensitive booleans/ints.
        wp_localize_script( 'cogu-protection', 'WPCGConfig', [
            'disableRightClick'   => (bool) $s['disable_right_click'],
            'disableCopy'         => (bool) $s['disable_copy'],
            'disableCut'          => (bool) $s['disable_cut'],
            'disableSelect'       => (bool) $s['disable_select'],
            'disableDrag'         => (bool) $s['disable_drag'],
            'disableDevtools'     => (bool) $s['disable_devtools_keys'],
            'disablePrint'        => (bool) $s['disable_print_keys'],
            'showPopup'           => (bool) $s['show_popup'],
            'popupDelay'          => (int)  $s['popup_delay'],
            'closeOnOverlay'      => (bool) $s['popup_close_on_overlay'],
            'ajaxUrl'             => admin_url( 'admin-ajax.php' ),
            'nonce'               => wp_create_nonce( 'cogu_subscribe' ),
        ] );
    }

    // -------------------------------------------------------------------------
    // Popup markup
    // -------------------------------------------------------------------------

    public function render_popup(): void {
        if ( ! COGU_Protection::instance()->should_protect() ) {
            return;
        }

        $s = COGU_Settings::all();

        if ( empty( $s['show_popup'] ) ) {
            return;
        }

        // Build overlay background style.
        $hex     = sanitize_hex_color( $s['popup_bg_color'] ) ?: '#000000';
        $opacity = max( 0, min( 100, (int) $s['popup_bg_opacity'] ) ) / 100;
        $rgba    = $this->hex_to_rgba( $hex, $opacity );

        ?>
        <div id="cogu-overlay" class="cogu-overlay" style="background:<?php echo esc_attr( $rgba ); ?>;" aria-hidden="true"></div>
        <div id="cogu-popup" class="cogu-popup" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Advertisement', 'content-guard' ); ?>" aria-hidden="true">
            <button class="cogu-close" id="cogu-close" aria-label="<?php esc_attr_e( 'Close advertisement', 'content-guard' ); ?>">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="cogu-popup-inner">
                <?php $this->render_ad( $s ); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Dispatch to the correct ad-type renderer.
     */
    private function render_ad( array $s ): void {
        $type = $s['popup_ad_type'] ?? 'html';
        switch ( $type ) {
            case 'banner':
                $this->render_banner( $s );
                break;
            case 'adsense':
                $this->render_adsense( $s );
                break;
            case 'subscribe':
                $this->render_subscribe( $s );
                break;
            case 'video':
                $this->render_video( $s );
                break;
            default:
                $this->render_html( $s );
        }
    }

    // --- Ad type: Banner Image ---
    private function render_banner( array $s ): void {
        $img_url = esc_url( $s['banner_image_url'] ?? '' );
        if ( empty( $img_url ) ) {
            echo '<p class="cogu-ad-empty">' . esc_html__( 'No banner image configured.', 'content-guard' ) . '</p>';
            return;
        }

        $link_url = esc_url( $s['banner_link_url'] ?? '' );
        $target   = esc_attr( $s['banner_link_target'] ?? '_blank' );
        $alt      = esc_attr( $s['banner_alt_text'] ?? '' );

        echo '<div class="cogu-ad-banner">';
        if ( $link_url ) {
            printf(
                '<a href="%s" target="%s" rel="noopener noreferrer">',
                esc_url( $link_url ),
                esc_attr( $target )
            );
        }
        printf( '<img src="%s" alt="%s" loading="lazy" />', esc_url( $img_url ), esc_attr( $alt ) );
        if ( $link_url ) {
            echo '</a>';
        }
        echo '</div>';
    }

    // --- Ad type: HTML / Rich Text ---
    private function render_html( array $s ): void {
        $content = $s['popup_html_content'] ?? '';
        if ( empty( $content ) ) {
            echo '<p class="cogu-ad-empty">' . esc_html__( 'No content configured.', 'content-guard' ) . '</p>';
            return;
        }
        echo '<div class="cogu-ad-html">' . wp_kses_post( $content ) . '</div>';
    }

    // --- Ad type: Google AdSense ---
    private function render_adsense( array $s ): void {
        $code = $s['adsense_code'] ?? '';
        if ( empty( $code ) ) {
            echo '<p class="cogu-ad-empty">' . esc_html__( 'No AdSense code configured.', 'content-guard' ) . '</p>';
            return;
        }

        // Output raw — already sanitized at save time to only contain
        // <script> and <ins> tags specific to AdSense.
        echo '<div class="cogu-ad-adsense">';
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized at save via wp_kses with script/ins allowlist.
        echo $code;
        echo '</div>';
    }

    // --- Ad type: Subscribe Form ---
    private function render_subscribe( array $s ): void {
        $title       = esc_html( $s['subscribe_title'] ?: esc_html__( 'Subscribe to our newsletter', 'content-guard' ) );
        $placeholder = esc_attr( $s['subscribe_placeholder'] ?: esc_html__( 'Enter your email', 'content-guard' ) );
        $btn_label   = esc_html( $s['subscribe_btn_label'] ?: esc_html__( 'Subscribe', 'content-guard' ) );

        ?>
        <div class="cogu-ad-subscribe">
            <?php if ( $title ) : ?>
                <h3 class="cogu-subscribe-title"><?php echo $title; // phpcs:ignore -- escaped above. ?></h3>
            <?php endif; ?>
            <div class="cogu-subscribe-msg" id="cogu-subscribe-msg" aria-live="polite"></div>
            <div class="cogu-subscribe-form" id="cogu-subscribe-form">
                <input
                    type="email"
                    id="cogu-email-input"
                    class="cogu-email-input"
                    placeholder="<?php echo $placeholder; // phpcs:ignore -- escaped above. ?>"
                    autocomplete="email"
                    required
                />
                <button type="button" class="cogu-subscribe-btn" id="cogu-subscribe-btn">
                    <?php echo $btn_label; // phpcs:ignore -- escaped above. ?>
                </button>
            </div>
        </div>
        <?php
    }

    // --- Ad type: Video ---
    private function render_video( array $s ): void {
        $type      = $s['video_type'] ?? 'mp4';
        $autoplay  = ! empty( $s['video_autoplay'] ) ? 'autoplay' : '';
        $muted     = ! empty( $s['video_muted'] ) ? 'muted' : '';

        echo '<div class="cogu-ad-video">';

        if ( 'embed' === $type ) {
            $embed_url = esc_url( $s['video_embed_url'] ?? '' );
            if ( empty( $embed_url ) ) {
                echo '<p class="cogu-ad-empty">' . esc_html__( 'No embed URL configured.', 'content-guard' ) . '</p>';
            } else {
                // Allow safe iframe for video embeds (YouTube / Vimeo only).
                $allowed_hosts = [ 'youtube.com', 'www.youtube.com', 'youtu.be', 'vimeo.com', 'player.vimeo.com' ];
                $host          = wp_parse_url( $embed_url, PHP_URL_HOST );
                if ( in_array( $host, $allowed_hosts, true ) ) {
                    printf(
                        '<iframe src="%s" allowfullscreen loading="lazy" title="%s" class="cogu-video-iframe"></iframe>',
                        esc_url( $embed_url ),
                        esc_attr__( 'Advertisement video', 'content-guard' )
                    );
                } else {
                    echo '<p class="cogu-ad-empty">' . esc_html__( 'Only YouTube and Vimeo embeds are supported.', 'content-guard' ) . '</p>';
                }
            }
        } else {
            $mp4_url = esc_url( $s['video_mp4_url'] ?? '' );
            if ( empty( $mp4_url ) ) {
                echo '<p class="cogu-ad-empty">' . esc_html__( 'No video file configured.', 'content-guard' ) . '</p>';
            } else {
                printf(
                    '<video %s %s controls playsinline class="cogu-video-mp4"><source src="%s" type="video/mp4">%s</video>',
                    esc_attr( $autoplay ),
                    esc_attr( $muted ),
                    esc_url( $mp4_url ),
                    esc_html__( 'Your browser does not support the video tag.', 'content-guard' )
                );
            }
        }

        echo '</div>';
    }

    // -------------------------------------------------------------------------
    // Subscribe AJAX handler
    // -------------------------------------------------------------------------

    public function handle_subscribe(): void {
        // Verify nonce — protects against CSRF.
        if ( ! check_ajax_referer( 'cogu_subscribe', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => __( 'Security check failed.', 'content-guard' ) ], 403 );
        }

        $email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
        if ( ! is_email( $email ) ) {
            wp_send_json_error( [ 'message' => __( 'Please enter a valid email address.', 'content-guard' ) ], 422 );
        }

        $s        = COGU_Settings::all();
        $mailto   = sanitize_email( $s['subscribe_mailto'] );
        $redirect = esc_url_raw( $s['subscribe_redirect_url'] );

        // Send email notification if configured.
        if ( $mailto ) {
            $subject = sprintf(
                /* translators: %s: site name */
                __( '[%s] New newsletter subscriber', 'content-guard' ),
                get_bloginfo( 'name' )
            );
            $message = sprintf(
                /* translators: %s: email address */
                __( 'New subscriber: %s', 'content-guard' ),
                $email
            );
            wp_mail( $mailto, $subject, $message );
        }

        /**
         * Fires after a successful subscribe form submission.
         *
         * @param string $email     Sanitized email address.
         * @param array  $settings  Full plugin settings array.
         */
        do_action( 'cogu_subscribe_submitted', $email, $s );

        wp_send_json_success( [
            'message'  => __( 'Thank you for subscribing!', 'content-guard' ),
            'redirect' => $redirect,
        ] );
    }

    // -------------------------------------------------------------------------
    // Utility
    // -------------------------------------------------------------------------

    private function hex_to_rgba( string $hex, float $opacity ): string {
        $hex = ltrim( $hex, '#' );
        if ( strlen( $hex ) === 3 ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec( substr( $hex, 0, 2 ) );
        $g = hexdec( substr( $hex, 2, 2 ) );
        $b = hexdec( substr( $hex, 4, 2 ) );
        return "rgba({$r},{$g},{$b},{$opacity})";
    }
}
