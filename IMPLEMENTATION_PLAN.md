# Implementation Plan — apk-directory-pro

Phase 0 discovery and requirement mapping for the Modern WordPress APK Directory Theme + companion plugin.

Source brief: `Cursor-WordPress-APK-Theme-Build-Brief.md`  
Working name / text domain: `apk-directory-pro`  
Companion plugin: `apk-directory-core`  
License: GPL-2.0-or-later  
Targets: WordPress ≥ 6.6, PHP 8.1–8.4

---

## 0. Discovery findings

| Item | Finding |
| --- | --- |
| Repository type | **New / greenfield** WordPress product repo |
| Existing code | Only stub `README.md` + git history (initial commit) |
| WordPress core / Docker | Not present yet — will be scaffolded in Phase 1 |
| Appyn 2.0.17 package | **Not present** in workspace or uploads |
| Reference site assets | Must not be scraped or copied (brief rule 8) |
| Branch strategy | Feature branches `cursor/<name>-1845` off `main` |

No existing theme, plugin, Composer, npm, PHPCS, or CI configuration to preserve beyond the stub README.

---

## 1. Proposed architecture

### 1.1 Package split (non-negotiable)

```text
/
├── apk-directory-core/          # Companion plugin (data + business logic)
├── apk-directory-pro/           # Classic PHP theme (presentation only)
├── bin/                         # Shared tooling (zip, pot, wp-env helpers)
├── .github/workflows/           # CI
├── docs/                        # Install, security, migration, Nginx/Apache
├── Cursor-WordPress-APK-Theme-Build-Brief.md
├── IMPLEMENTATION_PLAN.md
└── README.md
```

Release artifacts (not committed as binaries during development):

1. `dist/apk-directory-pro-theme.zip` — theme only  
2. `dist/apk-directory-core.zip` — plugin only  

**Rule:** CPT, taxonomies, meta, versions table, downloads, ratings, reports, REST, migrations live **only** in the plugin. Theme may query public APIs / helpers exposed by the plugin and degrade gracefully if the plugin is inactive.

### 1.2 Runtime stack

| Layer | Choice |
| --- | --- |
| Theme style | Classic PHP theme + `theme.json` + block editor support |
| PHP structure | Namespaced classes, Composer PSR-4 |
| Front-end JS | Vanilla ES modules; no jQuery dependency |
| CSS | Custom properties, Grid/Flexbox, logical properties, container queries |
| Fonts | Locally hosted system-font stack (no render-blocking Google Fonts) |
| Icons | Bundled SVG, accessible |
| Admin UI | Gutenberg plugin panels + classic metabox fallback |
| Settings | WordPress Settings API + Customizer where appropriate |
| Persistence | `register_post_meta`, custom table `{prefix}adp_versions`, comments (`adp_review`) or private CPT for reports |
| APIs | REST + Rewrite API + WP-Cron + Transients/Object Cache |
| Tooling | Composer, npm, PHPCS (WPCS), PHPStan, PHPUnit, ESLint, Stylelint, Playwright, Lighthouse CI |

### 1.3 Namespace map

| Package | Root namespace | Autoload |
| --- | --- | --- |
| Plugin | `Adp\Core\` | `apk-directory-core/src/` |
| Theme | `Adp\Theme\` | `apk-directory-pro/inc/` |

Bootstrap files stay thin:

- `apk-directory-core/apk-directory-core.php` — constants, autoload, activation hooks, `Plugin::init()`
- `apk-directory-pro/functions.php` — autoload + `Setup::init()` only

### 1.4 Request / data flow

```text
[Visitor]
   │
   ├─ Theme templates (SSR HTML, SEO)
   │     └─ Template tags / helpers → plugin public API
   │
   ├─ REST (search, ratings, reports, versions admin)
   │     └─ Controllers → Services → Repositories
   │
   └─ Download rewrite `/download/{token}/`
         └─ Signer validate → Counter (once) → stream / redirect

[Editor]
   └─ Gutenberg panels / Version manager
         └─ REST + Meta API + Versions Service (transactional current version)
```

### 1.5 Content model summary

| Entity | Storage |
| --- | --- |
| App | CPT `adp_app` — archive `/apps/`, single `/app/%postname%/` |
| Blog | Core `post` |
| Categories | Taxonomy `adp_app_category` |
| Developer | Taxonomy `adp_developer` |
| Platform | Taxonomy `adp_platform` |
| App tags | Taxonomy `adp_tag` |
| App fields | Post meta `_adp_*` via `register_post_meta` |
| Versions | Table `{prefix}adp_versions` via repository/service |
| Reviews | Comment type `adp_review` (+ rating meta) |
| Reports | Private CPT `adp_report` (or table if volume warrants) |
| Taxonomy meta | Term meta: icon, color, short description, SEO intro, featured, order |

### 1.6 Security baseline (cross-cutting)

- ABSPATH guards; nonces + capability + ownership checks  
- Sanitize in, escape out; prepared SQL; strict REST `permission_callback`  
- Rate limits: search, ratings, reports, downloads, submissions  
- Signed, expiring download tokens; no filesystem path exposure  
- APK upload capability `upload_apk_files` (not granted to Authors by default)  
- URL scheme allowlists; SSRF protections on server-side fetches  
- Uninstall prompts retention; never auto-delete app content  

---

## 2. Assumptions (non-blocking unless marked)

| # | Assumption | Rationale |
| --- | --- | --- |
| A1 | **Local APK hosting is supported** via Media Library attachments (`download_type=media`) **and** external/redirect modes. Legal/operational compliance is the site owner's responsibility; docs will warn clearly. | Brief designs all three download types; no legal counsel available in-repo. |
| A2 | **Appyn package is absent** — migration tool will implement mapping from the brief’s field list and accept an uploaded/path reference later; dry-run fixtures use synthetic Appyn-shaped meta. | Package not in workspace; brief forbids copying proprietary code. |
| A3 | Monorepo with sibling theme + plugin directories; ZIPs built by `bin/build-zips.sh`. | Cleanest for CI and dual-package releases. |
| A4 | Reviews use **comment type `adp_review`** first; migrate to custom table only if comment constraints block requirements. | Prefer core APIs. |
| A5 | Reports use **private CPT `adp_report`**. | Admin UI and retention simpler than a custom table for v1. |
| A6 | Demo content uses **neutral generated names/art** only — never scraped from APKFolder/APKPure/Appyn. | Brief rules 8–9. |
| A7 | Gambling/casino category notices are **configurable theme/plugin settings**, off by default except when those categories exist. | Policy-sensitive; owner-controlled. |
| A8 | Dark mode: `prefers-color-scheme` + localStorage toggle; FOUC avoided via inline critical theme class in `<head>`. | Brief §6. |
| A9 | Homepage section builder v1 = **Customizer / theme_mod ordered sections**; block patterns provided as progressive enhancement. Full block-based homepage builder can extend later without breaking SSR. | Keeps Phase 5 bounded and SEO-safe. |
| A10 | CAPTCHA for reports/reviews = **hook-based integration** only in v1 (no hard dependency on a CAPTCHA vendor). | Brief allows optional CAPTCHA through hooks. |

### Potential blockers (need owner input only if rejecting assumptions)

1. **A1 — Local APK hosting:** If the product must *never* store APK binaries on WordPress media, Phase 6 will ship external/redirect-only and disable media upload UI. Default plan: support all three modes.  
2. **Multisite / network activation:** Brief asks for multisite basics; assume per-site activation unless network-wide is required.  
3. **Jurisdiction / age-gate for casino APKs:** Assume configurable notice banners, not a full KYC/geo-block engine.

No architecture question is currently hard-blocking Phase 1 scaffolding under the assumptions above.

---

## 3. Phase map and status

| Phase | Name | Status |
| --- | --- | --- |
| 0 | Discovery and plan | **Complete** |
| 1 | Scaffolding | **Complete** |
| 2 | Data layer | **Complete** |
| 3 | Admin publishing UI | **Complete** |
| 4 | Theme foundation | **Complete** |
| 5 | Discovery pages | **Complete** |
| 6 | App and download pages | **Complete** |
| 7 | SEO, compatibility, migration | **Complete** |
| 8 | QA and release | **Complete** |

Work one phase at a time. At phase end: run checks, report changed files / remaining issues, commit.

---

## 4. Requirement → files → tests

### Phase 1 — Scaffolding

| Requirement | Files | Tests / checks |
| --- | --- | --- |
| Theme package skeleton | `apk-directory-pro/{style.css,theme.json,functions.php,index.php,composer.json,package.json,phpcs.xml.dist,readme.txt,.gitignore}` | PHP lint; theme headers valid |
| Plugin bootstrap | `apk-directory-core/{apk-directory-core.php,uninstall.php,composer.json,package.json,phpcs.xml.dist,readme.txt}` | Activation/deactivation hooks fire without fatals |
| PSR-4 autoload | Both `composer.json` + `vendor/` (dev) | Class load smoke test |
| CI workflow | `.github/workflows/ci.yml` | Syntax, PHPCS, PHPUnit jobs defined |
| ZIP builders | `bin/build-zips.sh` | Produces both ZIPs excluding vendor-dev, maps, secrets |
| WP test / env harness | `bin/install-wp-tests.sh` or `@wordpress/env` config | PHPUnit bootstrap loads |
| Root docs stub | `README.md` update (install overview only) | — |

**Exit criteria:** Both packages installable as ZIPs on clean WP; CI green for lint/autoload; no production feature code claimed complete.

---

### Phase 2 — Data layer

| Requirement | Files | Tests |
| --- | --- | --- |
| CPT `adp_app` | `src/Content/AppPostType.php` | Rewrite slugs, REST, supports, caps |
| Taxonomies + term meta | `src/Content/Taxonomies.php`, `src/Content/TermMeta.php` | Archives, REST, meta sanitizers |
| Post meta `_adp_*` | `src/Content/Meta.php`, `src/Content/MetaSchema.php` | register_post_meta schemas, auth, defaults, sanitizers |
| Capabilities | `src/Content/Capabilities.php` | `upload_apk_files`, APK Manager role |
| Versions table | `src/Versions/Schema.php` (dbDelta), activation migration | Table exists; columns match brief |
| Version repository/service | `src/Versions/Repository.php`, `Service.php` | CRUD, one-current-version invariant, prepared SQL, transactions |
| REST schemas (apps/versions) | `src/Rest/AppController.php`, `VersionsController.php` | permission_callback, validation |
| Fixtures | `tests/fixtures/*.php` | Factory helpers for apps/versions |
| Reviews storage hooks | `src/Reviews/Schema.php` (comment type registration) | Type registered; rating meta bounds 1–5 |
| Reports CPT | `src/Reports/ReportPostType.php` | Not public; admin-only |

**Exit criteria:** Unit/integration tests for meta, versions, caps pass before any UI work.

---

### Phase 3 — Admin publishing UI

| Requirement | Files | Tests |
| --- | --- | --- |
| Gutenberg panels | `assets/admin/js/editor-panels/*`, `src/Admin/EditorPanels.php` | Field validation (package name, URLs, dates, bytes) |
| Classic metabox fallback | `src/Admin/Metaboxes.php` | Renders when block editor disabled |
| Screenshot gallery | Admin media module | Sortable IDs saved as `_adp_screenshot_ids` |
| Version manager UI | `src/Versions/Admin.php`, admin assets | Add/Edit/Duplicate/Set current/Delete + confirm |
| SHA-256 status | `src/Downloads/HashService.php` | Hash computed for local attachments |
| Setup wizard | `src/Admin/SetupWizard.php` | Creates pages **once** on opt-in; flush rewrites on activation only |
| Demo importer | `src/Admin/DemoImporter.php` | Neutral demo only; idempotent |
| Notices / permissions | `src/Admin/Notices.php` | Role matrix: Author cannot upload APK by default |

**Exit criteria:** Keyboard-accessible panels; capability checks verified; no raw `$_POST` without nonce/sanitize.

---

### Phase 4 — Theme foundation

| Requirement | Files | Tests |
| --- | --- | --- |
| Setup / menus / widgets | `inc/Setup.php`, `inc/helpers.php` | Menu locations, widget areas registered |
| Assets enqueue | `inc/Assets.php`, `assets/css/*`, `assets/js/*` | No jQuery; defer strategy; filemtimes/manifest versions |
| Design tokens | `assets/css/tokens.css`, `theme.json` | Variables match brief §6 |
| Header / footer | `header.php`, `footer.php`, `template-parts/header/*`, `footer/*` | Sticky header, search shell, drawer a11y |
| Dark mode | `inc/Accessibility.php` + small JS module | prefers-color-scheme + toggle; no FOUC |
| Customizer | `inc/Customizer.php` | Sanitize callbacks, caps |
| Layout primitives | CSS + `template-parts/content/*` | 1180 / 760 columns; 44px targets |
| screenshot.png | Theme screenshot (original) | — |

**Exit criteria:** Theme activates alone without fatals; header/footer keyboard + RTL logical CSS smoke.

---

### Phase 5 — Discovery pages

| Requirement | Files | Tests |
| --- | --- | --- |
| Homepage sections | `front-page.php`, `template-parts/home/*`, Customizer section order | SSR sections 1–9; View all links |
| App cards | `template-parts/cards/app-*.php` | Icon, meta, rating only if real |
| Archives + taxonomies | `archive-adp_app.php`, `taxonomy-adp_*.php` | Sort/filter GET params; pagination |
| Search results | `search.php` | Apps + posts labeled |
| Live search | Theme JS + `src/Search/Controller.php` | ≥2 chars, 250ms debounce, max 8–10, AbortController, rate limit, cache |
| Grid/list toggle | Front JS (localStorage) | Progressive enhancement |
| Empty states | Template parts | Reset filters |
| Patterns/blocks (SSR) | `patterns/*`, plugin `blocks/*` | Render without JS |

**Exit criteria:** Playwright smoke at 375/768/1280; axe on home/archive/search; no `posts_per_page => -1`.

---

### Phase 6 — App and download pages

| Requirement | Files | Tests |
| --- | --- | --- |
| Single app template | `single-adp_app.php`, `template-parts/app/*` | Exact info order §7.4 |
| Gallery / lightbox | `assets/js/lightbox.js` + markup | Dialog a11y; responsive images |
| TOC | `inc/TemplateHooks.php` | H2/H3; disable option |
| Versions route | Rewrite + `templates/versions.php` or theme template | `/app/{slug}/versions/` |
| Related apps | Query helper | Exclude current; category/developer |
| Reviews UI | `comments.php` + `src/Reviews/Controller.php` | Nonce, honeypot, rate limit, one-per-window |
| Report form | `src/Reports/Controller.php` + form partial | Reasons enum; private storage; admin notify |
| Download interstitial | Rewrite + plugin template | Token pages noindex |
| Signer / delivery | `src/Downloads/Signer.php`, `Controller.php`, `Counter.php` | Expiry, scope, path traversal tests, once-count |
| Sticky mobile download bar | Theme CSS/JS | Safe-area; no obscuring essential UI |
| Trust strip | Template part | Factual labels only |

**Exit criteria:** Security tests for expired token, IDOR, open redirect, unauthorized file; core flow works without JS.

---

### Phase 7 — SEO, compatibility, migration

| Requirement | Files | Tests |
| --- | --- | --- |
| SoftwareApplication schema | `src/Schema/SoftwareApplication.php` | Only factual data; rating only if visible/real |
| BlogPosting conditional | `src/Schema/BlogPosting.php` | Skip if Yoast/Rank Math owns it |
| SEO plugin compat | Theme `inc/SeoCompatibility.php` | No duplicate title/canonical/OG |
| Breadcrumbs + schema | Theme + plugin helpers | Skip BreadcrumbList if SEO plugin outputs |
| Sitemaps / robots | `src/Seo/Sitemaps.php` | Exclude download tokens; include CPT/tax |
| Appyn migrator | `src/Migration/AppynMigrator.php` | Dry-run, batch, resume, idempotent, no auto-delete source |
| CLI | `src/Cli/Commands.php` | WP-CLI dry-run / import / status |
| Cache invalidation | `src/Cache/Invalidator.php` | Clears rails/counts on save |
| Privacy policy text | `src/Privacy.php` | Suggested policy snippets |

**Exit criteria:** Migration dry-run on fixtures; schema unit tests; Yoast/Rank Math coexistence documented/tested where env allows.

---

### Phase 8 — QA and release

| Requirement | Files / artifacts | Checks |
| --- | --- | --- |
| Full automated suite | CI + local | PHPCS, PHPStan, PHPUnit, ESLint, Stylelint, Playwright, axe, Lighthouse CI |
| Theme Check / Plugin Check | Reports in `docs/qa/` | Issues fixed or documented |
| Demo screenshots | `docs/screenshots/` (generated, original) | Home, archive, single |
| Release ZIPs | `dist/*.zip` | No dev deps, source maps, secrets, demo APKs, OS junk |
| Documentation | `docs/*.md`, theme/plugin `readme.txt` | Install, setup, update, backup, migration, child theme, APK storage rules, Nginx/Apache, troubleshooting |
| Final report | PR description + `docs/RELEASE_NOTES.md` | Known limitations honest |

**Definition of done:** Matches brief §16 exactly.

---

## 5. Detailed target file trees

### 5.1 Theme (`apk-directory-pro/`)

```text
apk-directory-pro/
├── style.css
├── theme.json
├── functions.php
├── index.php
├── front-page.php
├── home.php
├── archive-adp_app.php
├── taxonomy-adp_app_category.php
├── taxonomy-adp_developer.php
├── taxonomy-adp_platform.php
├── taxonomy-adp_tag.php
├── single-adp_app.php
├── search.php
├── page.php
├── single.php
├── 404.php
├── header.php
├── footer.php
├── comments.php
├── screenshot.png
├── assets/
│   ├── css/
│   │   ├── tokens.css
│   │   ├── base.css
│   │   ├── layout.css
│   │   ├── components.css
│   │   └── utilities.css
│   ├── js/
│   │   ├── main.js
│   │   ├── search.js
│   │   ├── filters.js
│   │   ├── dark-mode.js
│   │   ├── nav.js
│   │   ├── lightbox.js
│   │   └── reviews.js
│   ├── icons/
│   └── images/
├── inc/
│   ├── Setup.php
│   ├── Assets.php
│   ├── Customizer.php
│   ├── TemplateHooks.php
│   ├── SeoCompatibility.php
│   ├── Accessibility.php
│   └── helpers.php
├── template-parts/
│   ├── header/
│   ├── footer/
│   ├── app/
│   ├── cards/
│   ├── content/
│   ├── home/
│   └── navigation/
├── patterns/
├── languages/
├── tests/                    # theme-level PHPUnit / Playwright hooks as needed
├── composer.json
├── package.json
├── phpcs.xml.dist
├── phpstan.neon.dist
└── readme.txt
```

### 5.2 Plugin (`apk-directory-core/`)

```text
apk-directory-core/
├── apk-directory-core.php
├── uninstall.php
├── src/
│   ├── Plugin.php
│   ├── Activator.php
│   ├── Deactivator.php
│   ├── Content/
│   │   ├── AppPostType.php
│   │   ├── Taxonomies.php
│   │   ├── TermMeta.php
│   │   ├── Meta.php
│   │   ├── MetaSchema.php
│   │   └── Capabilities.php
│   ├── Versions/
│   │   ├── Schema.php
│   │   ├── Repository.php
│   │   ├── Service.php
│   │   └── Admin.php
│   ├── Downloads/
│   │   ├── Controller.php
│   │   ├── Signer.php
│   │   ├── Counter.php
│   │   └── HashService.php
│   ├── Reviews/
│   │   ├── Controller.php
│   │   └── Schema.php
│   ├── Reports/
│   │   ├── Controller.php
│   │   └── ReportPostType.php
│   ├── Search/
│   │   └── Controller.php
│   ├── Rest/
│   │   ├── AppController.php
│   │   └── VersionsController.php
│   ├── Schema/
│   │   ├── SoftwareApplication.php
│   │   └── BlogPosting.php
│   ├── Seo/
│   │   └── Sitemaps.php
│   ├── Admin/
│   │   ├── Settings.php
│   │   ├── SetupWizard.php
│   │   ├── DemoImporter.php
│   │   ├── EditorPanels.php
│   │   ├── Metaboxes.php
│   │   └── Notices.php
│   ├── Migration/
│   │   └── AppynMigrator.php
│   ├── Cache/
│   │   └── Invalidator.php
│   ├── Privacy.php
│   └── Cli/
│       └── Commands.php
├── assets/
├── blocks/
├── templates/                # download interstitial, etc. if rendered by plugin
├── languages/
├── tests/
│   ├── bootstrap.php
│   ├── Unit/
│   ├── Integration/
│   └── fixtures/
├── composer.json
├── package.json
├── phpcs.xml.dist
├── phpstan.neon.dist
└── readme.txt
```

---

## 6. Cross-cutting test matrix

| Concern | Approach |
| --- | --- |
| PHP syntax | `find … -name '*.php' -exec php -l` in CI |
| WPCS | PHPCS with `WordPress-Extra` (+ security sniffs where practical) |
| Static analysis | PHPStan level ≥ 5 (raise if feasible) |
| Unit | Versions service, signer, meta sanitizers, rate limiter |
| Integration | CPT/tax/meta registration, REST permissions, download flow |
| E2E | Playwright @ 375, 768, 1280, 1440 |
| a11y | axe on home, archive, search, app, versions, download, blog, page, 404 |
| Performance | Lighthouse CI home/archive/app; CSS &lt; 80KB / JS &lt; 50KB compressed budgets |
| Security | Expired token, path traversal, open redirect, XSS in search highlight, IDOR version download, capability probes |
| WP-CLI | Activate, rewrites, cron, migration dry-run, uninstall retention |
| Roles | Logged-out → Administrator + APK Manager |
| i18n/RTL | POT script; logical CSS; Urdu/Arabic sample in demo |

---

## 7. Visual system (theme tokens — original)

Do **not** copy APKFolder / APKPure / Appyn branding or CSS.

| Token | Value |
| --- | --- |
| `--adp-primary` | `#18a957` |
| `--adp-primary-dark` | `#128345` |
| `--adp-accent` | `#2563eb` |
| `--adp-bg` | `#f5f7fa` |
| `--adp-surface` | `#ffffff` |
| `--adp-text` | `#17202a` |
| `--adp-muted` | `#667085` |
| `--adp-border` | `#e4e7ec` |
| `--adp-warning` | `#f59e0b` |
| `--adp-danger` | `#dc2626` |
| Radii | 10 / 14 / 18 px |
| Max width | 1180px content / 760px reading |
| Spacing | 4px scale |
| Type | 16px base, ≥1.55 body lh, fluid `clamp` headings |
| Fonts | System stack (locally defined); no Google Fonts |

UI inspiration only: compact listing rows, clear download actions, dense discovery rails — **original** composition and components.

---

## 8. Risks and bounded alternatives

| Risk | Mitigation / alternative |
| --- | --- |
| Appyn migrator without real package | Ship dry-run + synthetic fixtures; document required meta keys; full validation when owner supplies a backup |
| Lighthouse 90 on shared CI hardware | Budget checks + local/demo evidence; document CI variance |
| APK MIME handling in WP | Custom upload mimes gated by capability; server rules in docs; never execute APKs in PHP |
| Comment-based reviews limits | Fallback path documented; table migration reserved |
| SEO plugin matrix | Feature-detect Yoast/Rank Math; never duplicate tags |
| Legal exposure of APK hosting | Docs + disclaimers; external URL mode; no scraping automation |

---

## 9. Out of scope / explicitly rejected (from brief)

- Bundling Google Drive / Microsoft Graph SDKs  
- Serialized mega-arrays for app data  
- Versions as child posts  
- Homegrown AMP theme  
- Fake ratings/views  
- Unbounded live search queries  
- Required ACF/Elementor/Redux/etc.  
- Scraping third-party stores  
- Arbitrary header/footer code injection by default  

---

## 10. Phase 0 deliverables checklist

- [x] Inspect workspace — greenfield repo confirmed  
- [x] Confirm project type — new theme + plugin product monorepo  
- [x] `IMPLEMENTATION_PLAN.md` with requirement → file → test mapping  
- [x] Assumptions recorded (A1–A10)  
- [x] Blockers identified (none hard-blocking under assumptions)  
- [x] Proposed architecture documented  
- [x] **Owner approval** received; Phases 1–8 implemented  

---

## 11. Next step

Implement **Phase 1 — Scaffolding** only:

1. Create theme + plugin package skeletons with Composer PSR-4  
2. Activation/deactivation hooks, uninstall stub  
3. PHPCS, PHPUnit bootstrap, npm lint tooling stubs, CI workflow  
4. `bin/build-zips.sh` producing both installable ZIPs  
5. Run syntax/autoload checks; commit; report files + next phase  

No production feature code (CPT/UI/templates) until Phase 1 exit criteria pass and Phase 2 begins.
