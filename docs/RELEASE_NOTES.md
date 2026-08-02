# Release Notes — 1.0.0

Initial release of **APK Directory Pro** (theme) and **APK Directory Core** (plugin).

## Packages

| Artifact | Contents |
|----------|----------|
| `apk-directory-core.zip` | Companion plugin (`apk-directory-core/`) with production Composer autoload |
| `apk-directory-pro-theme.zip` | Classic PHP theme (`apk-directory-pro/`) |

Build with `./bin/build-zips.sh`.

## Highlights

- App CPT `adp_app` with taxonomies (category, developer, platform, tags)
- Full `_adp_*` post meta via `register_post_meta` + REST schemas
- Dedicated `adp_versions` table, repository/service, admin version manager
- Local APK uploads **enabled by default**, plus external and redirect download types
- HMAC-signed download tokens, interstitial page, safe local streaming
- Live search REST (`adp/v1/search`), reviews, reports
- Gutenberg panels + classic metabox fallback
- Opt-in setup wizard and neutral demo importer
- Appyn migration tool with dry-run / batch / resume (source data never auto-deleted)
- Theme: homepage sections, archives/filters, single app layout, dark mode, RTL-ready CSS
- SEO helpers compatible with Yoast / Rank Math (no duplicate tags when those plugins own them)
- WP-CLI: `wp adp status`, `wp adp migrate`, `wp adp flush-rewrites`

## Verified in this release

- Clean WordPress 6.7.2 install from both ZIPs (activate plugin → theme)
- Local uploads default on; versions table created; rewrites for `/apps/`, `/app/{slug}/`, `/app/{slug}/versions/`, `/category/app/{term}/`, `/download/{token}/`
- Demo import (4 apps), setup pages (10), search REST, download stream headers
- App content survives theme switch away from APK Directory Pro
- PHPUnit: 29 tests, 54 assertions
- PHP syntax check on package sources

## Known limitations

1. **Appyn migration** maps fields from the build brief using synthetic/fixture-shaped meta. Validate against a real Appyn backup before production import.
2. **CAPTCHA** for reports/reviews is hook-based only (no bundled vendor).
3. **Lighthouse / Playwright / axe CI** configs are prepared; full browser CI matrix depends on the host environment.
4. **Gambling/casino notices** are configurable; geo-blocking / KYC is out of scope.
5. Block editor blocks are server-rendered with `block.json`; rich in-canvas inspectors are minimal.

## Security notes

- APK upload requires `upload_apk_files` (Administrators and APK Managers; not Authors by default).
- Download tokens expire and are scoped to a version ID.
- See [SECURITY-APK-STORAGE.md](SECURITY-APK-STORAGE.md) for Apache/Nginx rules.
