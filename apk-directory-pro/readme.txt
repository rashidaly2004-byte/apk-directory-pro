=== APK Directory Pro ===
Contributors: apkdirectorypro
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: custom-logo, custom-menu, featured-images, footer-widgets, rtl-language-support, translation-ready, block-patterns

A production-quality WordPress classic theme for app directory listings with app-store inspired UI.

== Description ==

APK Directory Pro is a presentation-only WordPress theme designed for app directory websites. It gracefully degrades when the companion plugin is inactive, showing blog content and standard WordPress features.

**Features:**

* App-store inspired listing UI with row and grid layouts
* Full Customizer integration for branding, homepage sections, and ads
* Dark mode with system preference detection and manual toggle
* WCAG 2.2 AA accessibility compliance
* RTL support with logical CSS properties
* Vanilla ES module JavaScript (no jQuery on frontend)
* SEO plugin compatibility (Yoast, Rank Math)
* Server-rendered homepage sections with configurable order

== Installation ==

1. Upload the `apk-directory-pro` folder to `/wp-content/themes/`
2. Activate the theme through the Appearance menu in WordPress
3. Configure settings via Appearance → Customize
4. For full app directory features, install and activate the companion plugin

== Development ==

Requires Composer for PHP dependencies:

`composer install`

Run linting:

`composer lint`

Generate translation template:

`wp i18n make-pot . languages/apk-directory-pro.pot --domain=apk-directory-pro`

Or with WP-CLI:

`wp i18n make-pot /path/to/apk-directory-pro languages/apk-directory-pro.pot --domain=apk-directory-pro`

== Changelog ==

= 1.0.0 =
* Initial release

== Credits ==

Built with WordPress coding standards and accessibility best practices.
