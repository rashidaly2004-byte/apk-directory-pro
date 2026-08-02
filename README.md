# APK Directory Pro

Production-ready WordPress theme and companion plugin for APK/app directory sites.

## Packages

| Package | Path | ZIP |
|---------|------|-----|
| Theme (presentation) | `apk-directory-pro/` | `dist/apk-directory-pro-theme.zip` |
| Plugin (data & logic) | `apk-directory-core/` | `dist/apk-directory-core.zip` |

## Requirements

- WordPress 6.6+
- PHP 8.1–8.4
- Pretty permalinks recommended

## Installation

1. Build or download both ZIP files from `dist/`.
2. Install **APK Directory Core** plugin first (Plugins → Add New → Upload).
3. Activate the plugin and complete the optional setup wizard.
4. Install **APK Directory Pro** theme (Appearance → Themes → Add New → Upload).
5. Activate the theme.
6. Visit Settings → Permalinks and save (if URLs do not work).

## Development

```bash
# Install dependencies
cd apk-directory-core && composer install
cd ../apk-directory-pro && composer install

# PHP syntax check
npm run lint:php

# Run plugin unit tests
npm run test:plugin

# Build release ZIPs
npm run build
```

## Architecture

App content (`adp_app` post type, taxonomies, metadata, version history, downloads, reviews) lives in the **plugin** so it survives theme changes. The **theme** handles templates, styling, search UI, and discovery pages.

## Security — APK storage

For locally hosted APK files:

- Grant `upload_apk_files` only to trusted roles (Administrators by default).
- Store files in `wp-content/uploads/` via the Media Library.
- Add web server rules to prevent PHP execution in upload directories.

### Apache (.htaccess in uploads)

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

Use Apps → Appyn Migration in the admin. Always run a dry-run preview first. Original Appyn data is never deleted automatically.

## Customization

Create a child theme of `apk-directory-pro` for CSS/template overrides. Do not modify plugin data registration in the theme.

## Troubleshooting

| Issue | Fix |
|-------|-----|
| 404 on app URLs | Re-save Permalinks; ensure plugin is active |
| Download links expire | Tokens expire after 1 hour; regenerate from app page |
| Missing app fields | Confirm APK Directory Core is active |

## License

GPL-2.0-or-later

## Known limitations

- Gutenberg editor panels are minimal; classic metabox provides full editing.
- Appyn migration supports dry-run preview; full batch import requires existing Appyn meta on the site.
- CAPTCHA for reports is hook-based (`adp_report_captcha`); no bundled provider.

See `IMPLEMENTATION_PLAN.md` for the full requirement mapping and phase status.
