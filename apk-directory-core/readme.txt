=== APK Directory Core ===
Contributors: apk-directory
Tags: apk, apps, directory
Requires at least: 6.6
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Companion plugin for APK Directory Pro theme — content types, versions, downloads, and ratings.

== Description ==

Registers the `adp_app` post type, taxonomies, metadata, version history table, secure downloads, reviews, reports, and REST API endpoints.

== Installation ==

1. Upload `apk-directory-core.zip` via Plugins → Add New → Upload.
2. Activate the plugin.
3. Run the optional setup wizard from Apps → Setup.

== Frequently Asked Questions ==

= Search or reporting fails for visitors in one country or on one network =

The search suggestion and report endpoints are rate limited per visitor address. If the site sits behind a CDN, load balancer, or reverse proxy, PHP sees the proxy address rather than the visitor, so everyone arriving through the same edge node shares a single allowance — which can look like a whole country being blocked. Mobile networks that put many subscribers behind one public address have the same effect.

Fix it by listing the proxy addresses or CIDR ranges under Apps → Settings → Trusted proxies, or by defining `ADP_TRUSTED_PROXIES` in `wp-config.php`:

`define( 'ADP_TRUSTED_PROXIES', '173.245.48.0/20, 103.21.244.0/22' );`

Use `*` to trust whatever proxy is in front of the site; this is only safe when the origin refuses traffic that does not arrive through it. Forwarded headers are ignored unless the request actually comes from a trusted proxy, so the setting cannot be used to spoof an address and evade a limit.

= How do I change or disable the rate limits? =

Filter them. A limit of `0` turns the check off:

`add_filter( 'adp_search_rate_limit', fn() => 60 );`
`add_filter( 'adp_search_rate_window', fn() => 60 );`
`add_filter( 'adp_report_rate_limit', fn() => 5 );`
`add_filter( 'adp_report_rate_window', fn() => 3600 );`

Note that the plugin never blocks by country and keeps no address blocklist. A site that is unreachable from one region — rather than just failing at search or reporting — is being blocked upstream: check the host firewall, the CDN WAF and its geo rules, and whether the domain is filtered by the local ISP.

== Changelog ==

= 1.0.0 =
* Initial release.
* Rate limits now identify visitors behind a trusted CDN or reverse proxy instead of counting every visitor sharing an edge node together.
