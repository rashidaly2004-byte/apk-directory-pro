#!/usr/bin/env bash
set -euo pipefail
export PATH="/usr/local/bin:/usr/bin:$PATH"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
echo "=== PHP lint ==="
find "$ROOT/apk-directory-core" "$ROOT/apk-directory-pro" -name '*.php' -not -path '*/vendor/*' -print0 | xargs -0 -n1 php -l
echo "=== PHPCS ==="
cd "$ROOT/apk-directory-core" && composer phpcs
cd "$ROOT/apk-directory-pro" && ./vendor/bin/phpcs --standard=phpcs.xml.dist --warning-severity=0
echo "=== PHPStan ==="
cd "$ROOT/apk-directory-core" && composer phpstan
echo "=== PHPUnit ==="
cd "$ROOT/apk-directory-core" && composer test
echo "=== All static checks passed ==="
