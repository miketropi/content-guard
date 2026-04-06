<?php
/**
 * Admin settings page view.
 *
 * Variables available: $s (settings array), $post_types, $categories, $updated.
 *
 * @package WPContentGuard
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap cogu-admin-wrap">

    <div class="cogu-header">
        <div class="cogu-header-logo">
            <span class="cogu-logo-icon">🛡</span>
            <div>
                <h1><?php esc_html_e( 'Content Guard', 'content-guard' ); ?></h1>
                <span class="cogu-version">v<?php echo esc_html( COGU_VERSION ); ?></span>
            </div>
        </div>
        <p class="cogu-header-desc"><?php esc_html_e( 'Copy Protection & Advertisement Popup', 'content-guard' ); ?></p>
    </div>

    <?php if ( $updated ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved successfully.', 'content-guard' ); ?></p></div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="cogu-settings-form">
        <input type="hidden" name="action" value="cogu_save">
        <?php wp_nonce_field( 'cogu_save_settings', 'cogu_nonce' ); ?>

        <div class="cogu-tabs-wrap">

            <!-- Tab nav -->
            <nav class="cogu-tab-nav" role="tablist">
                <?php
                $tabs = [
                    'protection'  => esc_html__( '🔒 Protection', 'content-guard' ),
                    'popup'       => esc_html__( '📢 Popup Ad', 'content-guard' ),
                    'scope'       => esc_html__( '🎯 Scope', 'content-guard' ),
                    'shortcode'   => esc_html__( '🔖 Shortcode', 'content-guard' ),
                ];

                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                foreach ( $tabs as $id => $label ) : ?>
                    <button type="button"
                        class="cogu-tab-btn<?php echo 'protection' === $id ? ' is-active' : ''; ?>"
                        data-tab="<?php echo esc_attr( $id ); ?>"
                        role="tab"
                        aria-selected="<?php echo 'protection' === $id ? 'true' : 'false'; ?>"
                        aria-controls="cogu-tab-<?php echo esc_attr( $id ); ?>">
                        <?php echo esc_html( $label ); ?>
                    </button>
                <?php endforeach; ?>
            </nav>

            <!-- ═══════════════════════════════ TAB: Protection ═══ -->
            <div id="cogu-tab-protection" class="cogu-tab-panel is-active" role="tabpanel">
                <table class="form-table cogu-form-table" role="presentation">
                    <?php
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                    $protection_opts = [
                        'disable_right_click'   => [ esc_html__( 'Disable Right Click', 'content-guard' ),   esc_html__( 'Block context menu on all content (href links still work)', 'content-guard' ) ],
                        'disable_copy'           => [ esc_html__( 'Disable Copy (Ctrl+C)', 'content-guard' ),  esc_html__( 'Block clipboard copy event', 'content-guard' ) ],
                        'disable_cut'            => [ esc_html__( 'Disable Cut (Ctrl+X)', 'content-guard' ),   esc_html__( 'Block clipboard cut event', 'content-guard' ) ],
                        'disable_select'         => [ esc_html__( 'Disable Text Selection', 'content-guard' ), esc_html__( 'Prevent selectstart on page (form inputs excluded)', 'content-guard' ) ],
                        'disable_drag'           => [ esc_html__( 'Disable Drag & Drop', 'content-guard' ),    esc_html__( 'Block dragstart for text and images', 'content-guard' ) ],
                        'disable_devtools_keys'  => [ esc_html__( 'Block DevTools Keys', 'content-guard' ),    esc_html__( 'F12, Ctrl+Shift+I/J/C, Ctrl+U', 'content-guard' ) ],
                        'disable_print_keys'     => [ esc_html__( 'Block Print Key (Ctrl+P)', 'content-guard' ), esc_html__( 'Disable keyboard print shortcut', 'content-guard' ) ],
                        'protect_logged_in'      => [ esc_html__( 'Protect Logged-in Users', 'content-guard' ), esc_html__( 'Uncheck to skip protection for logged-in users (e.g. admins)', 'content-guard' ) ],
                    ];

                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                    foreach ( $protection_opts as $key => [ $label, $desc ] ) : ?>
                        <tr>
                            <th scope="row"><?php echo esc_html( $label ); ?></th>
                            <td>
                                <label class="cogu-toggle-label">
                                    <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="0">
                                    <input type="checkbox"
                                        name="<?php echo esc_attr( $key ); ?>"
                                        value="1"
                                        class="cogu-toggle-input"
                                        <?php checked( ! empty( $s[ $key ] ) ); ?>>
                                    <span class="cogu-toggle-track"><span class="cogu-toggle-thumb"></span></span>
                                    <span class="cogu-toggle-desc"><?php echo esc_html( $desc ); ?></span>
                                </label>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div><!-- /protection -->

            <!-- ═══════════════════════════════ TAB: Popup Ad ═══ -->
            <div id="cogu-tab-popup" class="cogu-tab-panel" role="tabpanel" hidden>

                <table class="form-table cogu-form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Enable Popup', 'content-guard' ); ?></th>
                        <td>
                            <label class="cogu-toggle-label">
                                <input type="hidden" name="show_popup" value="0">
                                <input type="checkbox" name="show_popup" value="1" class="cogu-toggle-input" <?php checked( ! empty( $s['show_popup'] ) ); ?>>
                                <span class="cogu-toggle-track"><span class="cogu-toggle-thumb"></span></span>
                                <span class="cogu-toggle-desc"><?php esc_html_e( 'Show popup when a protection violation is triggered', 'content-guard' ); ?></span>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Close on Overlay Click', 'content-guard' ); ?></th>
                        <td>
                            <label class="cogu-toggle-label">
                                <input type="hidden" name="popup_close_on_overlay" value="0">
                                <input type="checkbox" name="popup_close_on_overlay" value="1" class="cogu-toggle-input" <?php checked( ! empty( $s['popup_close_on_overlay'] ) ); ?>>
                                <span class="cogu-toggle-track"><span class="cogu-toggle-thumb"></span></span>
                                <span class="cogu-toggle-desc"><?php esc_html_e( 'Clicking the dark overlay closes the popup', 'content-guard' ); ?></span>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="popup_delay"><?php esc_html_e( 'Popup Delay (ms)', 'content-guard' ); ?></label></th>
                        <td>
                            <input type="number" id="popup_delay" name="popup_delay"
                                value="<?php echo esc_attr( $s['popup_delay'] ); ?>"
                                min="0" max="10000" step="100" class="small-text">
                            <p class="description"><?php esc_html_e( 'Milliseconds between violation and popup appearing. 0 = instant.', 'content-guard' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="popup_bg_color"><?php esc_html_e( 'Overlay Color', 'content-guard' ); ?></label></th>
                        <td>
                            <input type="text" id="popup_bg_color" name="popup_bg_color"
                                value="<?php echo esc_attr( $s['popup_bg_color'] ); ?>"
                                class="cogu-color-picker">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="popup_bg_opacity"><?php esc_html_e( 'Overlay Opacity (%)', 'content-guard' ); ?></label></th>
                        <td>
                            <input type="number" id="popup_bg_opacity" name="popup_bg_opacity"
                                value="<?php echo esc_attr( $s['popup_bg_opacity'] ); ?>"
                                min="0" max="100" class="small-text">
                        </td>
                    </tr>
                </table>

                <hr class="cogu-divider">

                <!-- Ad type selector -->
                <h3 class="cogu-section-title"><?php esc_html_e( 'Advertisement Type', 'content-guard' ); ?></h3>
                <div class="cogu-ad-type-grid" role="group" aria-label="<?php esc_attr_e( 'Select advertisement type', 'content-guard' ); ?>">
                    <?php
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                    $ad_types = [
                        'html'      => [ '📝', esc_html__( 'HTML / Rich Text', 'content-guard' ),  esc_html__( 'Custom content with full editor', 'content-guard' ) ],
                        'banner'    => [ '🖼', esc_html__( 'Banner Image', 'content-guard' ),        esc_html__( 'Upload image with optional link', 'content-guard' ) ],
                        'adsense'   => [ '💰', esc_html__( 'Google AdSense', 'content-guard' ),      esc_html__( 'Paste your AdSense ad unit code', 'content-guard' ) ],
                        'subscribe' => [ '✉️', esc_html__( 'Subscribe Form', 'content-guard' ),      esc_html__( 'Email input + button with AJAX', 'content-guard' ) ],
                        'video'     => [ '🎬', esc_html__( 'Video Ad', 'content-guard' ),            esc_html__( 'Upload MP4 or embed YouTube/Vimeo', 'content-guard' ) ],
                    ];

                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                    foreach ( $ad_types as $type => [ $icon, $label, $desc ] ) : ?>
                        <label class="cogu-ad-type-card <?php echo esc_attr( $s['popup_ad_type'] === $type ? 'is-selected' : '' ); ?>">
                            <input type="radio" name="popup_ad_type" value="<?php echo esc_attr( $type ); ?>"
                                class="cogu-ad-type-radio"
                                <?php checked( esc_attr( $s['popup_ad_type'] ), esc_attr( $type ) ); ?>>
                            <span class="cogu-ad-type-icon"><?php echo esc_html( $icon ); ?></span>
                            <span class="cogu-ad-type-name"><?php echo esc_html( $label ); ?></span>
                            <span class="cogu-ad-type-desc"><?php echo esc_html( $desc ); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <!-- Ad type config panels -->

                <!-- HTML -->
                <div class="cogu-ad-config" data-ad-type="html" <?php echo esc_attr( $s['popup_ad_type'] !== 'html' ? 'hidden' : '' ); ?>>
                    <h4><?php esc_html_e( 'HTML / Rich Text Content', 'content-guard' ); ?></h4>
                    <?php
                    wp_editor(
                        wp_kses_post( $s['popup_html_content'] ),
                        'popup_html_content_editor',
                        [
                            'textarea_name' => 'popup_html_content',
                            'media_buttons' => true,
                            'textarea_rows' => 10,
                            'teeny'         => false,
                        ]
                    );
                    ?>
                </div>

                <!-- Banner -->
                <div class="cogu-ad-config" data-ad-type="banner" <?php echo esc_attr( $s['popup_ad_type'] !== 'banner' ? 'hidden' : '' ); ?>>
                    <h4><?php esc_html_e( 'Banner Image', 'content-guard' ); ?></h4>
                    <table class="form-table cogu-form-table" role="presentation">
                        <tr>
                            <th><label for="banner_image_url"><?php esc_html_e( 'Image', 'content-guard' ); ?></label></th>
                            <td>
                                <div class="cogu-media-row">
                                    <input type="url" id="banner_image_url" name="banner_image_url"
                                        value="<?php echo esc_url( $s['banner_image_url'] ); ?>"
                                        class="regular-text cogu-media-url" placeholder="https://...">
                                    <button type="button" class="button cogu-media-btn" data-target="banner_image_url" data-type="image">
                                        <?php esc_html_e( 'Choose Image', 'content-guard' ); ?>
                                    </button>
                                    <button type="button" class="button cogu-media-clear" data-target="banner_image_url">✕</button>
                                </div>
                                <div class="cogu-banner-preview" id="banner-preview">
                                    <?php if ( $s['banner_image_url'] ) : ?>
                                        <img src="<?php echo esc_url( $s['banner_image_url'] ); ?>" alt="">
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="banner_alt_text"><?php esc_html_e( 'Alt Text', 'content-guard' ); ?></label></th>
                            <td><input type="text" id="banner_alt_text" name="banner_alt_text" class="regular-text"
                                value="<?php echo esc_attr( $s['banner_alt_text'] ); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="banner_link_url"><?php esc_html_e( 'Link URL', 'content-guard' ); ?></label></th>
                            <td><input type="url" id="banner_link_url" name="banner_link_url" class="regular-text"
                                value="<?php echo esc_url( $s['banner_link_url'] ); ?>" placeholder="https://..."></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Link Target', 'content-guard' ); ?></th>
                            <td>
                                <select name="banner_link_target">
                                    <option value="_blank" <?php selected( $s['banner_link_target'], '_blank' ); ?>><?php esc_html_e( 'New tab (_blank)', 'content-guard' ); ?></option>
                                    <option value="_self"  <?php selected( $s['banner_link_target'], '_self' ); ?>><?php esc_html_e( 'Same tab (_self)', 'content-guard' ); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- AdSense -->
                <div class="cogu-ad-config" data-ad-type="adsense" <?php echo esc_attr( $s['popup_ad_type'] !== 'adsense' ? 'hidden' : '' ); ?>>
                    <h4><?php esc_html_e( 'Google AdSense Code', 'content-guard' ); ?></h4>
                    <p class="description"><?php esc_html_e( 'Paste your AdSense ad unit code below. Only <script> and <ins> tags are accepted.', 'content-guard' ); ?></p>
                    <textarea id="adsense_code" name="adsense_code" rows="8" class="large-text code"
                        placeholder="&lt;script async src=&quot;https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js&quot;&gt;&lt;/script&gt;&#10;&lt;ins class=&quot;adsbygoogle&quot; ...&gt;&lt;/ins&gt;"
                    ><?php echo esc_textarea( $s['adsense_code'] ); ?></textarea>
                </div>

                <!-- Subscribe -->
                <div class="cogu-ad-config" data-ad-type="subscribe" <?php echo esc_attr( $s['popup_ad_type'] !== 'subscribe' ? 'hidden' : '' ); ?>>
                    <h4><?php esc_html_e( 'Subscribe Form', 'content-guard' ); ?></h4>
                    <table class="form-table cogu-form-table" role="presentation">
                        <tr>
                            <th><label for="subscribe_title"><?php esc_html_e( 'Form Title', 'content-guard' ); ?></label></th>
                            <td><input type="text" id="subscribe_title" name="subscribe_title" class="regular-text"
                                value="<?php echo esc_attr( $s['subscribe_title'] ); ?>"
                                placeholder="<?php esc_attr_e( 'Subscribe to our newsletter', 'content-guard' ); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="subscribe_placeholder"><?php esc_html_e( 'Email Placeholder', 'content-guard' ); ?></label></th>
                            <td><input type="text" id="subscribe_placeholder" name="subscribe_placeholder" class="regular-text"
                                value="<?php echo esc_attr( $s['subscribe_placeholder'] ); ?>"
                                placeholder="<?php esc_attr_e( 'Enter your email', 'content-guard' ); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="subscribe_btn_label"><?php esc_html_e( 'Button Label', 'content-guard' ); ?></label></th>
                            <td><input type="text" id="subscribe_btn_label" name="subscribe_btn_label" class="regular-text"
                                value="<?php echo esc_attr( $s['subscribe_btn_label'] ); ?>"
                                placeholder="<?php esc_attr_e( 'Subscribe', 'content-guard' ); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="subscribe_mailto"><?php esc_html_e( 'Notify Email', 'content-guard' ); ?></label></th>
                            <td>
                                <input type="email" id="subscribe_mailto" name="subscribe_mailto" class="regular-text"
                                    value="<?php echo esc_attr( $s['subscribe_mailto'] ); ?>">
                                <p class="description"><?php esc_html_e( 'Receive a notification email for each subscriber. Leave blank to disable.', 'content-guard' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="subscribe_redirect_url"><?php esc_html_e( 'Redirect After Subscribe', 'content-guard' ); ?></label></th>
                            <td>
                                <input type="url" id="subscribe_redirect_url" name="subscribe_redirect_url" class="regular-text"
                                    value="<?php echo esc_url( $s['subscribe_redirect_url'] ); ?>" placeholder="https://...">
                                <p class="description"><?php esc_html_e( 'Optional. Leave blank to just show a success message.', 'content-guard' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Video -->
                <div class="cogu-ad-config" data-ad-type="video" <?php echo esc_attr( $s['popup_ad_type'] !== 'video' ? 'hidden' : '' ); ?>>
                    <h4><?php esc_html_e( 'Video Ad', 'content-guard' ); ?></h4>
                    <table class="form-table cogu-form-table" role="presentation">
                        <tr>
                            <th><?php esc_html_e( 'Video Source', 'content-guard' ); ?></th>
                            <td>
                                <label>
                                    <input type="radio" name="video_type" value="mp4" class="cogu-video-type-radio" <?php checked( $s['video_type'], 'mp4' ); ?>>
                                    <?php esc_html_e( 'Upload MP4 file', 'content-guard' ); ?>
                                </label>
                                &nbsp;&nbsp;
                                <label>
                                    <input type="radio" name="video_type" value="embed" class="cogu-video-type-radio" <?php checked( $s['video_type'], 'embed' ); ?>>
                                    <?php esc_html_e( 'Embed URL (YouTube / Vimeo)', 'content-guard' ); ?>
                                </label>
                            </td>
                        </tr>
                        <tr class="cogu-video-mp4-row" <?php echo esc_attr( 'embed' === $s['video_type'] ? 'hidden' : '' ); ?>>
                            <th><label for="video_mp4_url"><?php esc_html_e( 'MP4 File URL', 'content-guard' ); ?></label></th>
                            <td>
                                <div class="cogu-media-row">
                                    <input type="url" id="video_mp4_url" name="video_mp4_url"
                                        value="<?php echo esc_url( $s['video_mp4_url'] ); ?>"
                                        class="regular-text cogu-media-url" placeholder="https://...">
                                    <button type="button" class="button cogu-media-btn" data-target="video_mp4_url" data-type="video">
                                        <?php esc_html_e( 'Choose Video', 'content-guard' ); ?>
                                    </button>
                                    <button type="button" class="button cogu-media-clear" data-target="video_mp4_url">✕</button>
                                </div>
                            </td>
                        </tr>
                        <tr class="cogu-video-embed-row" <?php echo esc_attr( 'mp4' === $s['video_type'] ? 'hidden' : '' ); ?>>
                            <th><label for="video_embed_url"><?php esc_html_e( 'Embed URL', 'content-guard' ); ?></label></th>
                            <td>
                                <input type="url" id="video_embed_url" name="video_embed_url"
                                    value="<?php echo esc_url( $s['video_embed_url'] ); ?>"
                                    class="regular-text" placeholder="https://www.youtube.com/embed/...">
                                <p class="description"><?php esc_html_e( 'Use the embed URL (not the watch URL). YouTube: youtube.com/embed/ID · Vimeo: player.vimeo.com/video/ID', 'content-guard' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Playback', 'content-guard' ); ?></th>
                            <td>
                                <label>
                                    <input type="hidden" name="video_autoplay" value="0">
                                    <input type="checkbox" name="video_autoplay" value="1" <?php checked( ! empty( esc_attr( $s['video_autoplay'] ) ) ); ?>>
                                    <?php esc_html_e( 'Autoplay', 'content-guard' ); ?>
                                </label>
                                &nbsp;&nbsp;
                                <label>
                                    <input type="hidden" name="video_muted" value="0">
                                    <input type="checkbox" name="video_muted" value="1" <?php checked( ! empty( esc_attr( $s['video_muted'] ) ) ); ?>>
                                    <?php esc_html_e( 'Muted (required for autoplay in most browsers)', 'content-guard' ); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>

            </div><!-- /popup -->

            <!-- ═══════════════════════════════ TAB: Scope ═══ -->
            <div id="cogu-tab-scope" class="cogu-tab-panel" role="tabpanel" hidden>
                <table class="form-table cogu-form-table" role="presentation">
                    <tr>
                        <th><?php esc_html_e( 'Apply Protection To', 'content-guard' ); ?></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><?php esc_html_e( 'Apply Protection To', 'content-guard' ); ?></legend>
                                <label>
                                    <input type="radio" name="scope" value="all" class="cogu-scope-radio" <?php checked( $s['scope'], 'all' ); ?>>
                                    <strong><?php esc_html_e( 'Entire site', 'content-guard' ); ?></strong>
                                    — <?php esc_html_e( 'All public pages (use Exclusion list to exempt specific pages)', 'content-guard' ); ?>
                                </label><br><br>
                                <label>
                                    <input type="radio" name="scope" value="selected" class="cogu-scope-radio" <?php checked( $s['scope'], 'selected' ); ?>>
                                    <strong><?php esc_html_e( 'Selected areas only', 'content-guard' ); ?></strong>
                                    — <?php esc_html_e( 'Configure below', 'content-guard' ); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr class="cogu-scope-row" <?php echo 'all' === $s['scope'] ? 'hidden' : ''; ?>>
                        <th><?php esc_html_e( 'Post Types', 'content-guard' ); ?></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><?php esc_html_e( 'Post Types', 'content-guard' ); ?></legend>
                                <?php 
                                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                                foreach ( $post_types as $pt ) : ?>
                                    <label style="display:block;margin-bottom:6px;">
                                        <input type="checkbox"
                                            name="scope_post_types[]"
                                            value="<?php echo esc_attr( $pt->name ); ?>"
                                            <?php checked( in_array( $pt->name, (array) $s['scope_post_types'], true ) ); ?>>
                                        <?php echo esc_html( $pt->label ); ?>
                                        <code><?php echo esc_html( $pt->name ); ?></code>
                                    </label>
                                <?php endforeach; ?>
                            </fieldset>
                        </td>
                    </tr>

                    <tr class="cogu-scope-row" <?php echo 'all' === $s['scope'] ? 'hidden' : ''; ?>>
                        <th><label for="scope_page_ids"><?php esc_html_e( 'Specific Post/Page IDs', 'content-guard' ); ?></label></th>
                        <td>
                            <input type="text" id="scope_page_ids" name="scope_page_ids"
                                value="<?php echo esc_attr( $s['scope_page_ids'] ); ?>"
                                class="regular-text" placeholder="12, 45, 88">
                            <p class="description"><?php esc_html_e( 'Comma-separated IDs. Find ID in post/page edit URL.', 'content-guard' ); ?></p>
                        </td>
                    </tr>

                    <tr class="cogu-scope-row" <?php echo 'all' === $s['scope'] ? 'hidden' : ''; ?>>
                        <th><?php esc_html_e( 'Categories', 'content-guard' ); ?></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><?php esc_html_e( 'Categories', 'content-guard' ); ?></legend>
                                <div class="cogu-checkbox-scroll">
                                    <?php foreach ( $categories as $cat ) : ?>
                                        <label style="display:block;margin-bottom:5px;">
                                            <input type="checkbox"
                                                name="scope_categories[]"
                                                value="<?php echo esc_attr( $cat->term_id ); ?>"
                                                <?php checked( in_array( (int) $cat->term_id, array_map( 'intval', (array) $s['scope_categories'] ), true ) ); ?>>
                                            <?php echo esc_html( $cat->name ); ?>
                                            <span class="description">(<?php echo esc_html( $cat->count ); ?> posts)</span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="scope_exclude_ids"><?php esc_html_e( 'Exclude Page/Post IDs', 'content-guard' ); ?></label></th>
                        <td>
                            <input type="text" id="scope_exclude_ids" name="scope_exclude_ids"
                                value="<?php echo esc_attr( $s['scope_exclude_ids'] ); ?>"
                                class="regular-text" placeholder="5, 22">
                            <p class="description"><?php esc_html_e( 'These pages will never be protected, regardless of scope settings above.', 'content-guard' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div><!-- /scope -->

            <!-- ═══════════════════════════════ TAB: Shortcode ═══ -->
            <div id="cogu-tab-shortcode" class="cogu-tab-panel" role="tabpanel" hidden>
                <div class="cogu-shortcode-docs">
                    <h3><?php esc_html_e( 'Inline Content Locking', 'content-guard' ); ?></h3>
                    <p><?php esc_html_e( 'Wrap any content with the shortcode to apply protection only to that block.', 'content-guard' ); ?></p>

                    <div class="cogu-code-block">
                        <div class="cogu-code-label"><?php esc_html_e( 'Basic usage', 'content-guard' ); ?></div>
                        <code>[cogu_lock]Your protected content here.[/cogu_lock]</code>
                    </div>

                    <div class="cogu-code-block">
                        <div class="cogu-code-label"><?php esc_html_e( 'Login gate — hide content from non-logged-in visitors', 'content-guard' ); ?></div>
                        <code>[cogu_lock require_login="1" message="Please log in to view this."]Hidden content.[/cogu_lock]</code>
                    </div>

                    <div class="cogu-code-block">
                        <div class="cogu-code-label"><?php esc_html_e( 'Custom CSS class', 'content-guard' ); ?></div>
                        <code>[cogu_lock class="premium-content"]Protected content.[/cogu_lock]</code>
                    </div>

                    <div class="cogu-shortcode-attrs">
                        <h4><?php esc_html_e( 'Attributes', 'content-guard' ); ?></h4>
                        <table class="widefat striped">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Attribute', 'content-guard' ); ?></th>
                                    <th><?php esc_html_e( 'Values', 'content-guard' ); ?></th>
                                    <th><?php esc_html_e( 'Description', 'content-guard' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td><code>require_login</code></td><td><code>"0"</code> / <code>"1"</code></td><td><?php esc_html_e( 'Hide content server-side from logged-out visitors', 'content-guard' ); ?></td></tr>
                                <tr><td><code>message</code></td><td><?php esc_html_e( 'any text', 'content-guard' ); ?></td><td><?php esc_html_e( 'Message shown when require_login="1" and user is not logged in', 'content-guard' ); ?></td></tr>
                                <tr><td><code>class</code></td><td><?php esc_html_e( 'CSS class', 'content-guard' ); ?></td><td><?php esc_html_e( 'Extra CSS class added to the wrapper div', 'content-guard' ); ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- /shortcode -->

        </div><!-- .cogu-tabs-wrap -->

        <?php submit_button( __( 'Save Settings', 'content-guard' ), 'primary large', 'submit', true, [ 'id' => 'cogu-submit' ] ); ?>
    </form>

</div><!-- .cogu-admin-wrap -->
