# APK Directory

WordPress theme + companion plugin for building neutral APK/app directory sites.

## Packages

| Package | Path | Purpose |
|---------|------|---------|
| **APK Directory Pro** | `apk-directory-pro/` | Classic PHP theme, templates, Customizer |
| **APK Directory Core** | `apk-directory-core/` | CPT, versions, downloads, REST API |

## Requirements

- WordPress 6.6+
- PHP 8.1–8.4
- MySQL 5.7+ / MariaDB 10.3+

## Installation

### From ZIP (production)

1. Build or download release archives:
   ```bash
   chmod +x bin/build-zips.sh
   ./bin/build-zips.sh
   ```
2. In **WP Admin → Plugins → Add New → Upload**, install `dist/apk-directory-core.zip` and activate.
3. In **Appearance → Themes → Add New → Upload**, install `dist/apk-directory-pro-theme.zip` and activate.
4. Visit **Apps → Setup** to create recommended pages and optional demo content.
5. Go to **Settings → Permalinks** and click **Save** once if pretty URLs do not work.

### From source (development)

```bash
cd apk-directory-core && composer install
cd ../apk-directory-pro && composer install   # optional, for PHPStan/PHPCS
```

Symlink or copy both folders into `wp-content/plugins/apk-directory-core` and `wp-content/themes/apk-directory-pro`.

Activate the plugin first, then the theme.

## Setup wizard

After activation, open **Apps → Setup** in wp-admin:

- Creates pages: Home, Apps, Games, Blog, About, Contact, Privacy, Disclaimer, DMCA/Takedown, Submit App
- Assigns primary and footer menus (when using APK Directory Pro)
- Optionally imports neutral demo apps (no third-party brand names)

Rewrite rules are flushed **once** on plugin activation only.

## Development

```bash
# Plugin unit tests
cd apk-directory-core && ./vendor/bin/phpunit

# Update translation templates
chmod +x bin/generate-pot.sh
./bin/generate-pot.sh

# Production ZIPs
./bin/build-zips.sh
```

See also:

- [docs/INSTALL.md](docs/INSTALL.md)
- [docs/SECURITY-APK-STORAGE.md](docs/SECURITY-APK-STORAGE.md)
- [docs/MIGRATION.md](docs/MIGRATION.md)
- [docs/RELEASE_NOTES.md](docs/RELEASE_NOTES.md)

## License

GPL-2.0-or-later
