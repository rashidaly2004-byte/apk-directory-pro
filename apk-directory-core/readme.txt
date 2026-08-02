=== APK Directory Core ===
Contributors: adp
Tags: apk, apps, directory, downloads
Requires at least: 6.6
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Content types, metadata, versions, downloads, ratings, and business logic for APK Directory Pro.

== Description ==

APK Directory Core is the companion plugin for the APK Directory Pro theme. It registers the `adp_app` custom post type, taxonomies, app metadata, version history table, secure downloads, reviews, reports, REST API endpoints, and migration tools.

**Features:**

* App custom post type with full metadata schema
* Dedicated versions table with one-current-version enforcement
* HMAC-signed expiring download tokens with local, external, and redirect modes
* Comment-based reviews with ratings
* Private report post type for takedown requests
* Live search REST endpoint with caching and rate limiting
* Appyn migration tool with dry-run and batch support
* Gutenberg editor panels and classic metabox fallback
* Server-rendered blocks for app listings and download buttons

== Installation ==

1. Upload the plugin ZIP via Plugins → Add New → Upload.
2. Activate APK Directory Core.
3. Complete the setup wizard under Apps → Setup.
4. Install and activate APK Directory Pro theme for front-end templates.

== Frequently Asked Questions ==

= Does this plugin work without the theme? =

Yes. All content remains in WordPress if you switch themes. Front-end templates require the companion theme or custom templates.

= Can I host APK files locally? =

Yes. Local uploads via the Media Library are enabled by default. Grant the `upload_apk_files` capability to roles that should upload APK binaries.

== Changelog ==

= 1.0.0 =
* Initial release.
