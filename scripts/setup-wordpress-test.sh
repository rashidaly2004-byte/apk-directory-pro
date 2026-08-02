#!/usr/bin/env bash
set -euo pipefail

export PATH="/usr/local/bin:$PATH"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
WP_DIR="${WP_DIR:-$ROOT/.wordpress-test}"
PORT="${WP_PORT:-8080}"

cd "$WP_DIR"

if [ ! -f wp-config.php ]; then
  wp config create \
    --dbname=adp_test \
    --dbuser=adp \
    --dbpass=adp \
    --dbhost=127.0.0.1 \
    --skip-check

  wp core install \
    --url="http://127.0.0.1:$PORT" \
    --title="APK Directory Test" \
    --admin_user=admin \
    --admin_password=adminpass \
    --admin_email=admin@example.com \
    --skip-email
fi

mkdir -p wp-content/themes wp-content/plugins
ln -sfn "$ROOT/apk-directory-pro" wp-content/themes/apk-directory-pro
ln -sfn "$ROOT/apk-directory-core" wp-content/plugins/apk-directory-core

composer install --working-dir="$ROOT/apk-directory-core" --no-interaction --quiet 2>/dev/null || true

wp plugin activate apk-directory-core
wp theme activate apk-directory-pro
wp rewrite structure '/%postname%/' --hard
wp rewrite flush --hard

# Demo app for e2e single page tests
if ! wp post list --post_type=adp_app --format=count 2>/dev/null | grep -q '[1-9]'; then
  APP_ID=$(wp post create --post_type=adp_app --post_title='Demo Navigator App' --post_status=publish --porcelain)
  wp post meta update "$APP_ID" _adp_short_description 'A neutral demo app for testing.'
  wp post meta update "$APP_ID" _adp_current_version '1.0.0'
  wp post meta update "$APP_ID" _adp_file_size_bytes 10485760
  wp post meta update "$APP_ID" _adp_android_requirement 'Android 8.0+'
  wp post meta update "$APP_ID" _adp_package_name 'com.demo.navigator'
fi

echo "Ready: http://127.0.0.1:$PORT"
