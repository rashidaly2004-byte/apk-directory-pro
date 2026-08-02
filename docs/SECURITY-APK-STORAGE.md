# APK Storage Security

Local APK and package files must **not** be directly web-accessible. The APK Directory stack delivers files through signed download tokens, not public attachment URLs.

## Apache (.htaccess)

Add inside `wp-content/uploads/` (or a dedicated `apk-storage/` subdirectory):

```apache
# Block direct download of package archives
<FilesMatch "\.(apk|xapk|apks|obb)$">
    Require all denied
</FilesMatch>

# Allow WordPress image/thumbnail generation for screenshots only
<FilesMatch "\.(jpe?g|png|gif|webp)$">
    Require all granted
</FilesMatch>
```

If using `mod_access_compat`:

```apache
<FilesMatch "\.(apk|xapk|apks|obb)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

## Nginx

Inside the `server` block, before the general PHP handler:

```nginx
location ~* /wp-content/uploads/.*\.(apk|xapk|apks|obb)$ {
    return 403;
}
```

Serve downloads only through WordPress:

```
/download/{signed-token}/
```

## Additional hardening

1. Store APKs in a non-public directory and reference via `attachment_id` only.
2. Keep `local_uploads_enabled` on only when needed.
3. Restrict `edit_adp_apps` and upload capabilities to trusted editors.
4. Use HTTPS everywhere; download tokens are time-limited.
5. Monitor `adp_reports` for malware or copyright flags.

## External downloads

When `download_type` is `external` or `redirect`, configure `external_url_allowlist` in plugin settings to limit outbound domains.
