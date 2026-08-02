#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN="${ROOT}/apk-directory-core"
THEME="${ROOT}/apk-directory-pro"

if ! command -v wp >/dev/null 2>&1; then
  echo "WP-CLI not found; generating minimal POT via xgettext fallback" >&2
  mkdir -p "${PLUGIN}/languages"
  xgettext \
    --language=PHP \
    --from-code=UTF-8 \
    --keyword=__ --keyword=_e --keyword=_n:1,2 --keyword=_x:1,2c --keyword=esc_html__ --keyword=esc_attr__ \
    --output="${PLUGIN}/languages/apk-directory-core.pot" \
    $(find "${PLUGIN}/src" "${PLUGIN}/blocks" "${PLUGIN}/templates" -name '*.php' 2>/dev/null)
  exit 0
fi

wp i18n make-pot "${PLUGIN}" "${PLUGIN}/languages/apk-directory-core.pot" \
  --domain=apk-directory-core \
  --exclude=vendor,tests,node_modules

wp i18n make-pot "${THEME}" "${THEME}/languages/apk-directory-pro.pot" \
  --domain=apk-directory-pro \
  --exclude=vendor,node_modules

echo "POT files updated."
