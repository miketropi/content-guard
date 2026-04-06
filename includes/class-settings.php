<?php
/**
 * Settings manager.
 *
 * Single source of truth for all plugin options.
 * All sanitization lives here so Admin and REST both use the same rules.
 *
 * @package WPContentGuard
 */

defined( 'ABSPATH' ) || exit;

final class COGU_Settings {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    // -------------------------------------------------------------------------
    // Defaults
    // -------------------------------------------------------------------------

    public static function defaults(): array {
        return [
            // --- Protection toggles ---
            'disable_right_click'    => 1,
            'disable_copy'           => 1,
            'disable_cut'            => 1,
            'disable_select'         => 1,
            'disable_drag'           => 1,
            'disable_devtools_keys'  => 1,
            'disable_print_keys'     => 1,

            // --- Scope ---
            'scope'                  => 'all',   // 'all' | 'selected'
            'scope_post_types'       => [],
            'scope_page_ids'         => '',
            'scope_categories'       => [],
            'scope_exclude_ids'      => '',
            'protect_logged_in'      => 1,       // 0 = skip logged-in users

            // --- Popup ---
            'show_popup'             => 1,
            'popup_delay'            => 500,
            'popup_bg_color'         => '#000000',
            'popup_bg_opacity'       => 70,
            'popup_close_on_overlay' => 1,

            // --- Popup ad type ---
            // 'banner' | 'html' | 'adsense' | 'subscribe' | 'video'
            'popup_ad_type'          => 'html',

            // Banner ad
            'banner_image_url'       => '',      // URL of uploaded image
            'banner_link_url'        => '',
            'banner_link_target'     => '_blank',
            'banner_alt_text'        => '',

            // HTML/Rich-text ad
            'popup_html_content'     => '',

            // AdSense ad
            'adsense_code'           => '',

            // Subscribe form
            'subscribe_title'        => '',
            'subscribe_placeholder'  => '',
            'subscribe_btn_label'    => '',
            'subscribe_redirect_url' => '',      // redirect after submit (optional)
            'subscribe_mailto'       => '',      // email to receive submissions

            // Video ad
            'video_type'             => 'mp4',   // 'mp4' | 'embed'
            'video_mp4_url'          => '',      // URL of uploaded .mp4
            'video_embed_url'        => '',      // YouTube / Vimeo embed URL
            'video_autoplay'         => 0,
            'video_muted'            => 1,
        ];
    }

    // -------------------------------------------------------------------------
    // Getters
    // -------------------------------------------------------------------------

    public static function all(): array {
        $saved = get_option( COGU_OPTION_KEY, [] );
        if ( ! is_array( $saved ) ) {
            $saved = [];
        }
        return wp_parse_args( $saved, self::defaults() );
    }

    public static function get( string $key, mixed $fallback = null ): mixed {
        $settings = self::all();
        return array_key_exists( $key, $settings ) ? $settings[ $key ] : $fallback;
    }

    // -------------------------------------------------------------------------
    // Sanitize & save
    // -------------------------------------------------------------------------

    /**
     * Sanitize raw POST data and return clean array ready to save.
     * Throws nothing — just cleans; caller handles update_option().
     *
     * @param array $raw Raw $_POST data (already slashed by WP).
     */
    public static function sanitize( array $raw ): array {
        $defaults = self::defaults();
        $clean    = [];

        // Boolean toggles.
        foreach ( [
            'disable_right_click', 'disable_copy', 'disable_cut',
            'disable_select', 'disable_drag', 'disable_devtools_keys',
            'disable_print_keys', 'show_popup', 'popup_close_on_overlay',
            'protect_logged_in', 'video_autoplay', 'video_muted',
        ] as $key ) {
            $clean[ $key ] = empty( $raw[ $key ] ) ? 0 : 1;
        }

        // Integers.
        $clean['popup_delay']      = max( 0, min( 10000, (int) ( $raw['popup_delay'] ?? $defaults['popup_delay'] ) ) );
        $clean['popup_bg_opacity'] = max( 0, min( 100,   (int) ( $raw['popup_bg_opacity'] ?? $defaults['popup_bg_opacity'] ) ) );

        // Color.
        $color                   = sanitize_hex_color( $raw['popup_bg_color'] ?? '' );
        $clean['popup_bg_color'] = $color ?: $defaults['popup_bg_color'];

        // Scope.
        $clean['scope'] = in_array( $raw['scope'] ?? 'all', [ 'all', 'selected' ], true )
            ? $raw['scope']
            : 'all';

        // Post types — intersect with registered.
        $valid_pt                  = array_keys( get_post_types( [ 'public' => true ] ) );
        $selected_pt               = (array) ( $raw['scope_post_types'] ?? [] );
        $clean['scope_post_types'] = array_values( array_intersect( array_map( 'sanitize_key', $selected_pt ), $valid_pt ) );

        // ID lists (comma-separated positive integers).
        $clean['scope_page_ids']    = self::sanitize_id_list( $raw['scope_page_ids'] ?? '' );
        $clean['scope_exclude_ids'] = self::sanitize_id_list( $raw['scope_exclude_ids'] ?? '' );

        // Categories — array of positive ints.
        $cats                      = array_map( 'absint', (array) ( $raw['scope_categories'] ?? [] ) );
        $clean['scope_categories'] = array_values( array_filter( $cats ) );

        // Popup ad type.
        $valid_types             = [ 'banner', 'html', 'adsense', 'subscribe', 'video' ];
        $clean['popup_ad_type']  = in_array( $raw['popup_ad_type'] ?? 'html', $valid_types, true )
            ? $raw['popup_ad_type']
            : 'html';

        // Banner ad.
        $clean['banner_image_url']   = esc_url_raw( $raw['banner_image_url'] ?? '' );
        $clean['banner_link_url']    = esc_url_raw( $raw['banner_link_url'] ?? '' );
        $clean['banner_link_target'] = in_array( $raw['banner_link_target'] ?? '_blank', [ '_blank', '_self' ], true )
            ? $raw['banner_link_target']
            : '_blank';
        $clean['banner_alt_text']    = sanitize_text_field( $raw['banner_alt_text'] ?? '' );

        // HTML content — allow full post-level HTML.
        $clean['popup_html_content'] = wp_kses_post( $raw['popup_html_content'] ?? '' );

        // AdSense — strip everything except <script> tags with google adsense src.
        // WP.org requirement: do NOT execute arbitrary script; just store & output raw.
        // We allow only the specific adsense pattern and sanitize everything else out.
        $clean['adsense_code'] = self::sanitize_adsense( $raw['adsense_code'] ?? '' );

        // Subscribe form.
        $clean['subscribe_title']        = sanitize_text_field( $raw['subscribe_title'] ?? '' );
        $clean['subscribe_placeholder']  = sanitize_text_field( $raw['subscribe_placeholder'] ?? '' );
        $clean['subscribe_btn_label']    = sanitize_text_field( $raw['subscribe_btn_label'] ?? '' );
        $clean['subscribe_redirect_url'] = esc_url_raw( $raw['subscribe_redirect_url'] ?? '' );
        $clean['subscribe_mailto']       = sanitize_email( $raw['subscribe_mailto'] ?? '' );

        // Video.
        $clean['video_type']      = in_array( $raw['video_type'] ?? 'mp4', [ 'mp4', 'embed' ], true )
            ? $raw['video_type']
            : 'mp4';
        $clean['video_mp4_url']   = esc_url_raw( $raw['video_mp4_url'] ?? '' );
        $clean['video_embed_url'] = esc_url_raw( $raw['video_embed_url'] ?? '' );

        return $clean;
    }

    public static function save( array $data ): bool {
        return update_option( COGU_OPTION_KEY, $data, false );
    }

    public static function delete(): void {
        delete_option( COGU_OPTION_KEY );
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private static function sanitize_id_list( string $raw ): string {
        $ids = array_filter(
            array_map( 'absint', explode( ',', $raw ) ),
            fn( int $id ) => $id > 0
        );
        return implode( ',', array_unique( $ids ) );
    }

    /**
     * Only allow AdSense <script> tags. Everything else is stripped.
     * This intentionally does NOT execute the script — it stores the string
     * and outputs it verbatim in the popup HTML via wp_kses with script allowed.
     */
    private static function sanitize_adsense( string $raw ): string {
        // Strip all tags except script, ins.
        $allowed = [
            'script' => [ 'async' => true, 'src' => true, 'crossorigin' => true ],
            'ins'    => [
                'class'                  => true,
                'style'                  => true,
                'data-ad-client'         => true,
                'data-ad-slot'           => true,
                'data-ad-format'         => true,
                'data-full-width-responsive' => true,
            ],
        ];
        return wp_kses( $raw, $allowed );
    }
}
