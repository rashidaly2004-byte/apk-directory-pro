#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DIST="$ROOT/dist"
mkdir -p "$DIST"

build_plugin() {
	local tmp
	tmp=$(mktemp -d)
	cp -r "$ROOT/apk-directory-core" "$tmp/apk-directory-core"
	cd "$tmp/apk-directory-core"
	if command -v composer >/dev/null 2>&1; then
		composer install --no-dev --optimize-autoloader --no-interaction 2>/dev/null || true
	fi
	rm -rf tests .github node_modules package-lock.json 2>/dev/null || true
	cd "$tmp"
	zip -rq "$DIST/apk-directory-core.zip" apk-directory-core -x "*.git*"
	rm -rf "$tmp"
}

build_theme() {
	local tmp
	tmp=$(mktemp -d)
	cp -r "$ROOT/apk-directory-pro" "$tmp/apk-directory-pro"
	cd "$tmp/apk-directory-pro"
	if command -v composer >/dev/null 2>&1; then
		composer install --no-dev --optimize-autoloader --no-interaction 2>/dev/null || true
	fi
	rm -rf tests .github node_modules package-lock.json 2>/dev/null || true
	cd "$tmp"
	zip -rq "$DIST/apk-directory-pro-theme.zip" apk-directory-pro -x "*.git*"
	rm -rf "$tmp"
}

build_plugin
build_theme

echo "Built:"
ls -la "$DIST"
