# APK Directory Pro — Implementation Plan

## Assumptions

1. **Local APK hosting** is supported but optional; external/redirect downloads are equally valid.
2. **MOD content** fields exist but are gated by a site policy setting.
3. **Demo content** uses neutral placeholder names and generated SVG icons, not scraped assets.
4. **CAPTCHA** for reports is hook-based; no third-party CAPTCHA bundled.
5. **Appyn theme** is not in this repository; migration reads legacy meta if present on migrated sites.
6. **PHP 8.1+** and **WordPress 6.6+** are the target runtime.

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│  apk-directory-pro (theme) — presentation only                │
│  Templates, CSS, JS, Customizer, block patterns, breadcrumbs  │
└──────────────────────────┬──────────────────────────────────┘
                           │ reads adp_app, taxonomies, REST
┌──────────────────────────▼──────────────────────────────────┐
│  apk-directory-core (plugin) — data & business logic        │
│  CPT, taxonomies, meta, versions table, downloads, reviews    │
└─────────────────────────────────────────────────────────────┘
```

Content survives theme switches because all app data lives in the plugin.

---

## Requirement → File/Test Mapping

### Phase 1 — Scaffolding

| Requirement | Files | Tests |
|-------------|-------|-------|
| Theme package | `apk-directory-pro/**` | Theme Check, PHP lint |
| Plugin package | `apk-directory-core/**` | Plugin Check, PHP lint |
| PSR-4 autoload | `composer.json` (both) | PHPUnit bootstrap |
| Build/ZIP scripts | `scripts/build-zips.sh` | CI workflow |
| PHPCS | `phpcs.xml.dist` (both) | `composer phpcs` |
| CI | `.github/workflows/ci.yml` | GitHub Actions |

### Phase 2 — Data Layer

| Requirement | Files | Tests |
|-------------|-------|-------|
| `adp_app` CPT | `Content/AppPostType.php` | `tests/Content/AppPostTypeTest.php` |
| Taxonomies | `Content/Taxonomies.php` | `tests/Content/TaxonomiesTest.php` |
| Post meta | `Content/Meta.php` | `tests/Content/MetaTest.php` |
| Version table | `Versions/Repository.php`, migrations in `Plugin.php` | `tests/Versions/RepositoryTest.php` |
| Version service | `Versions/Service.php` | `tests/Versions/ServiceTest.php` |
| Capabilities | `Content/Capabilities.php` | integration test |
| REST schemas | meta `show_in_rest`, custom routes | REST tests |

### Phase 3 — Admin UI

| Requirement | Files | Tests |
|-------------|-------|-------|
| Gutenberg panels | `assets/js/editor.js`, `Admin/EditorPanels.php` | manual + e2e |
| Classic metabox | `Admin/Metabox.php` | manual |
| Version manager | `Versions/Admin.php`, `assets/js/version-manager.js` | PHPUnit + e2e |
| Setup wizard | `Admin/SetupWizard.php` | WP-CLI activation test |
| Settings API | `Admin/Settings.php` | settings sanitization tests |
| Demo importer | `Admin/DemoImporter.php` | dry-run test |

### Phase 4 — Theme Foundation

| Requirement | Files | Tests |
|-------------|-------|-------|
| Theme setup | `inc/Setup.php` | activation smoke |
| Assets | `inc/Assets.php` | Lighthouse budget |
| Customizer | `inc/Customizer.php` | sanitization |
| Design tokens | `assets/css/main.css`, `theme.json` | Stylelint |
| Header/footer | `header.php`, `footer.php`, `template-parts/**` | axe, Playwright |
| Dark mode | `assets/js/theme-mode.js` | manual |
| Accessibility | `inc/Accessibility.php` | axe |

### Phase 5 — Discovery Pages

| Requirement | Files | Tests |
|-------------|-------|-------|
| Homepage | `front-page.php`, `template-parts/home/**` | Playwright 1280/375 |
| App archive | `archive-adp_app.php` | pagination test |
| Taxonomy archives | `taxonomy-*.php` | canonical URL test |
| Filters/sort | `assets/js/archive-filters.js` | GET param test |
| Live search | `Search/Controller.php`, `assets/js/search.js` | REST rate limit test |
| Search results | `search.php` | e2e |
| App cards | `template-parts/cards/app-card.php` | visual regression |

### Phase 6 — App & Download Pages

| Requirement | Files | Tests |
|-------------|-------|-------|
| Single app | `single-adp_app.php`, `template-parts/app/**` | Playwright |
| Version history | `templates/versions.php`, rewrite | rewrite test |
| Download interstitial | `Downloads/Controller.php`, `templates/download.php` | security tests |
| Signed tokens | `Downloads/Signer.php` | expiry/IDOR tests |
| Download counter | `Downloads/Counter.php` | privacy test |
| Reviews | `Reviews/Controller.php` | nonce/rate limit |
| Reports | `Reports/Controller.php` | honeypot test |
| Lightbox/gallery | `assets/js/lightbox.js` | axe keyboard |
| TOC | `template-parts/app/toc.php` | a11y |

### Phase 7 — SEO & Migration

| Requirement | Files | Tests |
|-------------|-------|-------|
| SoftwareApplication schema | `Schema/SoftwareApplication.php` | schema validation |
| SEO plugin compat | `inc/SeoCompatibility.php` | no duplicate meta |
| Breadcrumbs | `template-parts/navigation/breadcrumbs.php` | single H1 check |
| Robots/noindex | download/search pages | wp_robots test |
| Appyn migration | `Migration/AppynMigrator.php` | dry-run + idempotency |
| WP-CLI | `Cli/Commands.php` | CLI test |
| Cache invalidation | hooks on save | transient test |

### Phase 8 — QA & Release

| Requirement | Files | Tests |
|-------------|-------|-------|
| PHPUnit suite | `tests/**` | `composer test` |
| Playwright | `e2e/**` | CI |
| axe | `e2e/a11y/**` | CI |
| Lighthouse CI | `.lighthouserc.json` | CI |
| Release ZIPs | `scripts/build-zips.sh` | install on clean WP |
| Documentation | `README.md`, `readme.txt` (both) | manual review |

---

## Phase Status

| Phase | Status | Notes |
|-------|--------|-------|
| 0 | Complete | This document |
| 1 | Complete | Scaffolding, CI, build scripts |
| 2 | Complete | CPT, taxonomies, meta, versions table |
| 3 | Complete | Metabox, version manager, setup wizard |
| 4 | Complete | Theme setup, header, footer, tokens |
| 5 | Complete | Homepage, archives, search REST + UI |
| 6 | Complete | Single app, downloads, reviews, reports |
| 7 | Partial | Schema, migration dry-run; full batch import pending |
| 8 | Partial | Unit tests scaffolded; Playwright/Lighthouse CI pending |

---

## Blockers

None identified. Local APK hosting is optional and supported via Media Library with `upload_apk_files` capability.

## ZIP Output Paths

- `dist/apk-directory-pro-theme.zip`
- `dist/apk-directory-core.zip`
