# APK Directory Pro

Production-ready WordPress theme and companion plugin for APK/app directory sites.

## Packages

| Package | Path | ZIP |
|---------|------|-----|
| Theme | `apk-directory-pro/` | `dist/apk-directory-pro-theme.zip` |
| Plugin | `apk-directory-core/` | `dist/apk-directory-core.zip` |

## Requirements

- WordPress 6.6+
- PHP 8.1–8.4
- Pretty permalinks recommended

## Installation

1. Upload and activate **apk-directory-core** (`dist/apk-directory-core.zip`).
2. Upload and activate **apk-directory-pro** theme (`dist/apk-directory-pro-theme.zip`).
3. Run optional setup: **Apps → Setup**.
4. Save **Settings → Permalinks** if URLs 404.

## Features

### Plugin (`apk-directory-core`)

- `adp_app` post type with full metadata (`_adp_*`)
- Taxonomies: categories, developers, platforms, tags
- Custom `adp_versions` table with version manager UI
- Signed download URLs with interstitial page
- REST: live search, reviews, reports
- Gutenberg sidebar panels + classic metabox
- Appyn migration: dry-run, batch import, rollback map, logs
- `SoftwareApplication` schema when no SEO plugin active

### Theme (`apk-directory-pro`)

- Homepage sections (trending, updates, games, editor's choice, blog)
- App archives with sort/filters and grid/list toggle
- Live search with keyboard navigation
- Single app page: gallery/lightbox, TOC, version history, report form
- Dark mode, responsive header/drawer, WCAG-focused markup
- `screenshot.png` for WordPress admin preview

## Development

```bash
# Dependencies
cd apk-directory-core && composer install
cd ../apk-directory-pro && composer install
npm install

# Quality checks
cd apk-directory-core
composer phpcs      # PHPCS (errors only)
composer phpstan    # PHPStan level 0 + baseline
composer test       # PHPUnit (8 tests)

npm run lint:php    # PHP syntax all files

# Local WordPress test instance
./scripts/setup-wordpress-test.sh
cd .wordpress-test && wp server --host=127.0.0.1 --port=8080

# E2E (375px–1440px), accessibility, Lighthouse
WP_BASE_URL=http://127.0.0.1:8080 npx playwright test --config=playwright.config.js
WP_BASE_URL=http://127.0.0.1:8080 npx lhci autorun

# Plugin Check
wp plugin check apk-directory-core --ignore-codes=WordPress.WP.I18n.TextDomainMismatch

# Theme Check
wp eval-file scripts/theme-check-run.php

# Release ZIPs
./scripts/build-zips.sh
```

## Security — APK storage

Grant `upload_apk_files` only to trusted roles. Store APKs via Media Library.

### Apache (`uploads/.htaccess`)

```apache
<FilesMatch "\.(php|phtml|php8?)$">
    Require all denied
</FilesMatch>
```

### Nginx

```nginx
location ~* /wp-content/uploads/.*\.(php|phtml)$ {
    deny all;
}
```

## Migration from Appyn

**Apps → Appyn Migration**

1. Backup database.
2. Run **Dry Run Preview**.
3. Run **Full Migration** (batched, resumable via **Continue batch**).
4. Use **Rollback Migration** to remove migrated copies only — original Appyn data is preserved.

## Troubleshooting

| Issue | Fix |
|-------|-----|
| 404 on app URLs | Activate plugin; re-save Permalinks |
| Download link expired | Tokens expire after 1 hour |
| Report form fails | Ensure REST API reachable; check nonce |
| Plugin Check text domain | Expected: brief uses `apk-directory-pro` for both packages |

## License

GPL-2.0-or-later
