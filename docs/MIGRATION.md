# Migration Guide

## From Appyn / legacy APK themes

APK Directory Core includes an Appyn migrator (`Adp\Core\Migration\AppynMigrator`).

### WP-CLI dry run

```bash
wp adp migrate-appyn --dry-run
```

### Mapping reference

| Legacy meta | Core meta |
|-------------|-----------|
| `version` / `app_version` | `_adp_current_version` |
| `package` | `_adp_package_name` |
| `size` / `file_size` | `_adp_file_size_bytes` |
| `downloads` | `_adp_download_count` |
| Gallery IDs | `_adp_screenshot_ids` |

After migration:

1. Run setup wizard if pages are missing.
2. Flush rewrites: `wp adp flush-rewrites`
3. Re-save permalinks in wp-admin.

## From another APK directory plugin

1. Export apps as `adp_app` posts if possible.
2. Align meta keys to `MetaSchema` (`_adp_*` prefix).
3. Import version rows into the `wp_adp_versions` table via REST or admin Version Manager.

## Theme switch to APK Directory Pro

1. Activate APK Directory Core first.
2. Activate APK Directory Pro.
3. Reassign menus in **Appearance → Menus** if the setup wizard was not run.
4. Configure homepage section order in **Customizer → Homepage**.

## Database version upgrades

Plugin activation runs `VersionSchema::create_table()` when `adp_core_db_version` changes. No manual SQL is required for standard upgrades.
