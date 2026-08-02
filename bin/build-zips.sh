#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DIST="${ROOT}/dist"
STAGE="${ROOT}/.build"

rm -rf "${STAGE}" "${DIST}"
mkdir -p "${DIST}" "${STAGE}/plugin/apk-directory-core" "${STAGE}/theme/apk-directory-pro"

echo "==> Staging plugin"
rsync -a \
  --exclude='.git' \
  --exclude='vendor' \
  --exclude='node_modules' \
  --exclude='tests' \
  --exclude='.phpunit.cache' \
  --exclude='*.map' \
  --exclude='.DS_Store' \
  --exclude='demo-apks' \
  --exclude='*.apk' \
  --exclude='phpunit.xml.dist' \
  --exclude='phpcs.xml.dist' \
  --exclude='phpstan.neon.dist' \
  --exclude='composer.lock' \
  --exclude='package.json' \
  --exclude='.gitignore' \
  "${ROOT}/apk-directory-core/" "${STAGE}/plugin/apk-directory-core/"

echo "==> Generating production autoload for plugin"
(
  cd "${STAGE}/plugin/apk-directory-core"
  composer dump-autoload -o --no-dev --classmap-authoritative 2>/dev/null || composer dump-autoload -o --no-dev
)

echo "==> Building plugin ZIP"
(
  cd "${STAGE}/plugin"
  zip -rq "${DIST}/apk-directory-core.zip" apk-directory-core \
    -x '*.git*' '*/tests/*' '*/.phpunit.cache/*' '*/node_modules/*'
)

echo "==> Staging theme"
rsync -a \
  --exclude='.git' \
  --exclude='vendor' \
  --exclude='node_modules' \
  --exclude='tests' \
  --exclude='.phpunit.cache' \
  --exclude='*.map' \
  --exclude='.DS_Store' \
  --exclude='demo-apks' \
  --exclude='*.apk' \
  --exclude='phpunit.xml.dist' \
  --exclude='phpcs.xml.dist' \
  --exclude='phpstan.neon.dist' \
  --exclude='composer.lock' \
  --exclude='package.json' \
  --exclude='.gitignore' \
  "${ROOT}/apk-directory-pro/" "${STAGE}/theme/apk-directory-pro/"

echo "==> Building theme ZIP"
(
  cd "${STAGE}/theme"
  zip -rq "${DIST}/apk-directory-pro-theme.zip" apk-directory-pro \
    -x '*.git*' '*/node_modules/*' '*/vendor/*'
)

rm -rf "${STAGE}"
echo "==> Done"
ls -lh "${DIST}"
unzip -l "${DIST}/apk-directory-core.zip" | head -15
echo "---"
unzip -l "${DIST}/apk-directory-pro-theme.zip" | head -15
