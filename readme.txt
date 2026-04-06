=== Content Guard - Copy Protection & Advertisement Popup ===
Contributors: beplus
Tags: content protection, copy protection, disable right click, no right click, advertisement popup
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Protect your WordPress content and show a customizable ad popup when copy or right-click is attempted.

== Description ==

**Content Guard** prevents content theft and turns every violation into a revenue opportunity — showing your advertisement popup the moment someone tries to copy, right-click, or inspect your content.

**Protection features:**

* Disable right-click context menu (links remain functional)
* Block copy (Ctrl+C) and cut (Ctrl+X) keyboard shortcuts
* Disable text selection on page (CSS + JS, form inputs excluded)
* Block image and text drag & drop
* Block DevTools keyboard shortcuts (F12, Ctrl+Shift+I/J/C, Ctrl+U)
* Block print shortcut (Ctrl+P)
* Optional: skip protection for logged-in users

**Advertisement popup — 5 types:**

1. **HTML / Rich Text** — full WordPress editor, any content
2. **Banner Image** — upload image + optional click-through link
3. **Google AdSense** — paste your ad unit code directly
4. **Subscribe Form** — email input with AJAX submission and optional redirect
5. **Video Ad** — upload MP4 or embed YouTube / Vimeo

**Popup settings:**

* Enable/disable popup independently of protection
* Configurable delay (0 = instant)
* Custom overlay color and opacity
* Close on overlay click option
* Accessibility-compliant (ARIA roles, Escape key, focus management)

**Selective protection:**

* Apply to entire site or selected areas only
* Filter by post type, specific page/post IDs, or categories
* Exclusion list — exempt specific pages even when site-wide mode is on

**Shortcode:**

Use `[cogu_lock]` for inline selective protection anywhere in your content. Supports a login gate to hide content from non-logged-in visitors server-side.

`[cogu_lock]Your protected content.[/cogu_lock]`
`[cogu_lock require_login="1" message="Please log in."]Content.[/cogu_lock]`

**Technical highlights:**

* Zero jQuery dependency — pure vanilla JS
* OOP architecture with PSR-style autoloading
* No external HTTP requests or CDN calls
* Full i18n / translation ready
* Multisite compatible (uninstall cleans all sites)
* GPL-2.0+ licensed

== Installation ==

1. Upload the `content-guard` folder to `/wp-content/plugins/`.
2. Activate the plugin in **Plugins → Installed Plugins**.
3. Configure at **Settings → Content Guard**.

== Frequently Asked Questions ==

= Does this prevent all content theft? =
No JavaScript-based protection is 100% foolproof. Determined users can always view source via the browser's address bar or use screenshot tools. This plugin blocks the most common casual copying methods and deters the majority of users.

= Does it affect SEO? =
No. Search engine crawlers are not affected because they do not execute JavaScript. Your content remains fully indexable.

= Does it work with page builders (Elementor, Divi, etc.)? =
Yes. The plugin adds a script and CSS to the frontend — it has no dependency on specific page builders.

= Can I protect only specific pages? =
Yes. Use the **Scope** tab to target specific post types, page IDs, or categories. You can also use the `[cogu_lock]` shortcode for inline control.

= Where does subscribe form data go? =
The plugin sends an email notification to the address you configure. No data is stored in the database. You can use the `cogu_subscribe_submitted` action hook to integrate with your own CRM or email service.

= Is this plugin multisite compatible? =
Yes. Uninstalling removes settings from all sites in the network.

== Screenshots ==

1. Settings page — Protection tab
2. Settings page — Advertisement Popup tab with ad type selector
3. Settings page — Scope tab
4. Frontend popup — Subscribe form example
5. Frontend popup — Banner image example

== Changelog ==

= 1.0.0 =
* Initial release.
* 5 advertisement popup types: HTML, Banner, AdSense, Subscribe, Video.
* Selective scope: post type, page IDs, categories.
* [cogu_lock] shortcode with login gate.
* Vanilla JS, zero jQuery dependency.
* Full accessibility support (ARIA, keyboard navigation).
* Multisite compatible uninstall.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
