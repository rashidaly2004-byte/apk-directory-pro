#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DIST="$ROOT/dist"
mkdir -p "$DIST"

strip_dev_artifacts() {
	local dir="$1"
	rm -rf \
		"$dir/tests" \
		"$dir/.github" \
		"$dir/node_modules" \
		"$dir/vendor/bin" \
		2>/dev/null || true
	rm -f \
		"$dir/phpcs.xml.dist" \
		"$dir/phpstan.neon.dist" \
		"$dir/phpstan-baseline.neon" \
		"$dir/phpunit.xml.dist" \
		"$dir/.phpunit.result.cache" \
		"$dir/composer.json" \
		"$dir/composer.lock" \
		"$dir/package.json" \
		"$dir/package-lock.json" \
		2>/dev/null || true
	find "$dir" -name '.DS_Store' -delete 2>/dev/null || true
	find "$dir" -name '.gitkeep' -delete 2>/dev/null || true
}

build_plugin() {
	local tmp
	tmp=$(mktemp -d)
	cp -r "$ROOT/apk-directory-core" "$tmp/apk-directory-core"
	cd "$tmp/apk-directory-core"
	if command -v composer >/dev/null 2>&1; then
		composer install --no-dev --optimize-autoloader --no-interaction --quiet 2>/dev/null || true
	fi
	strip_dev_artifacts "$tmp/apk-directory-core"
	cd "$tmp"
	rm -f "$DIST/apk-directory-core.zip"
	zip -rq "$DIST/apk-directory-core.zip" apk-directory-core -x "*.git*"
	rm -rf "$tmp"
}

build_theme() {
	local tmp
	tmp=$(mktemp -d)
	cp -r "$ROOT/apk-directory-pro" "$tmp/apk-directory-pro"
	cd "$tmp/apk-directory-pro"
	if command -v composer >/dev/null 2>&1; then
		composer install --no-dev --optimize-autoloader --no-interaction --quiet 2>/dev/null || true
	fi
	strip_dev_artifacts "$tmp/apk-directory-pro"
	cd "$tmp"
	rm -f "$DIST/apk-directory-pro-theme.zip"
	zip -rq "$DIST/apk-directory-pro-theme.zip" apk-directory-pro -x "*.git*"
	rm -rf "$tmp"
}

build_plugin
build_theme

echo "Built:"
ls -la "$DIST"
