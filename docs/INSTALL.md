# Installation Guide

## Quick start

1. Upload and activate **APK Directory Core** (plugin).
2. Upload and activate **APK Directory Pro** (theme).
3. Run **Apps → Setup** and check **Create recommended pages**.
4. Save permalinks once under **Settings → Permalinks**.

## Recommended wp-admin configuration

| Setting | Value |
|---------|-------|
| Front page | Page created by setup wizard (`Home`) |
| Posts page | `Blog` page from wizard |
| Permalink structure | Post name (or any structure supporting `%postname%`) |

## File permissions

- `wp-content/uploads/` must be writable for local APK uploads and demo screenshots.
- Do not place APK files in the theme or plugin directories.

## Versions archive URL

Each app exposes a public version history at:

```
/app/{app-slug}/versions/
```

Pagination uses `?paged=2` when more than 20 versions exist.

## Uninstall

The plugin respects the `adp_core_settings` retention options. See plugin `uninstall.php` for options removed on deletion.

## Troubleshooting

| Issue | Fix |
|-------|-----|
| 404 on `/app/...` or `/download/...` | Re-save permalinks; or `wp adp flush-rewrites` |
| Downloads fail for local files | Confirm attachment exists and uploads directory is readable |
| Search returns nothing | Ensure plugin is active; REST route `adp/v1/search` must be reachable |
