# Peptide Starter Theme - Changelog

## [2.1.6] - 2026-04-27 — Our Methodology page template + footer link

### Added

- `page-our-methodology.php` — two-column sticky ToC template for `/our-methodology/`.
  Mirrors `page-how-we-review.php` layout; CSS classes use `page-our-methodology` prefix.
  The `#last-verified` anchor on this page is the deep-link target for Phase 2
  verdict-card "Last verified" lines.
- Footer "Our Method" section (section 5) now lists **Our Methodology** (`/our-methodology/`)
  between How We Review Peptides and Editorial Standards.

### Notes

- No changes to existing templates, CSS, or JS beyond the footer list addition.

## [2.1.5] - 2026-04-27 — Featured verdicts vertical-rhythm fix

### Changed
- `.hero-featured-verdicts__grid`: grid `gap` increased from `--spacing-xl` (32px) to
  `--spacing-2xl` (48px). Cards were too close together, making the three verdict cards
  read as a single visual block rather than three distinct entries.
- `.hero-verdict-card__title`: `margin-top: calc(var(--spacing-sm) - var(--spacing-md))`
  added (−8px offset). The verdict badge and title are a label/heading pair; the uniform
  card `gap: --spacing-md` (16px) created too much separation between them. Effective
  badge→title gap is now 8px; title→summary gap remains 16px.


## [2.1.4] - 2026-04-26 — B2: Coming soon badges on inactive tool cards

### Added
- `ps-module-card__badge` CSS class — absolute-positioned pill badge overlaid top-right of inactive cards; `--gray-200` bg, `--gray-700` text, Inter 600, 10px, pill shape
- `coming_soon` key in module card definitions; Protocol Builder, Tracker, Subject Log marked `true`
- Badge renders on top of existing `--inactive` visual treatment (dim + no-hover); does not replace it

### Changed
- `position: relative` added to `.ps-module-card` to enable badge absolute positioning
- Calculator card: `coming_soon` remains `false`; `available` resolves `true` via `shortcode_exists('prc_calculator')` — card now fully active with no dim, no badge, full hover
## [2.0.0-alpha.3] - 2026-04-26 — CMO walkthrough P1 fixes

### Fixed
- `template-parts/verdict/card.php`: Verdict text was a hardcoded generic placeholder.
  Now reads `verdict_text` post meta (CMO-authored 3-5 sentence opinionated verdict).
  Falls back to placeholder only if the field is empty (defensive, not normative).
- `template-parts/verdict/card.php`: Evidence-row glyphs were hardcoded by row position
  (row 1=✓, row 2=!, row 3=?), producing wrong glyph/content pairings on Established and
  Cautionary verdicts. Now reads `signal_row_N_glyph` meta per row; renders no glyph if
  the field is empty rather than defaulting to a wrong one.
- `single-peptide.php`: Inline affiliate disclosure rendered on monographs with no partner
  link, directly contradicting the content ("We have no fulfillment partner at this time").
  Now conditional on `has_partner_link` boolean meta (default false). Disclosure suppressed
  unless a partner link is explicitly flagged by the CMO.
- `header.php`: Dark-mode toggle was live in the header. Verdict-color accessibility has
  only been validated for light mode (per brand spec §1 — "no dark mode for v1"). Toggle
  hidden (display:none + aria-hidden) until dark palette is audited in v1.1.

### Added
- `inc/verdict-meta.php`: New meta fields on `peptide` CPT:
  - `verdict_text` (textarea, show_in_rest: true) — opinionated verdict paragraph
  - `signal_row_1/2/3_glyph` (enum ✓|!|?|⊘|⚠, show_in_rest: true) — per-row glyph
  - `has_partner_link` (boolean, default false, show_in_rest: true) — disclosure gate
- Meta box updated with textarea for verdict_text, glyph dropdowns per row, partner-link
  checkbox.
- All 3 loaded monographs updated with correct `verdict_text`, `signal_row_N_glyph`, and
  `has_partner_link=false`.


## [2.0.0-alpha.2] - 2026-04-26 — Wire verdict card and disclosure into single-peptide.php

### Fixed
- `single-peptide.php`: Verdict card component was created in alpha.1 but never called
  from the template — no verdict state was rendering on any peptide page. Now called
  immediately after the page header for all `pr_peptide` posts with a `verdict_state`.
- `single-peptide.php`: Affiliate disclosure (inline context) was created in alpha.1 but
  never called. Now rendered directly after the content box (below `the_content()`),
  ensuring it appears below any partner links in the post body per the editorial
  disclosure spec.
- `single-peptide.php`: Duplicate `<h1>` issue — the page header was rendering an H1
  title AND the verdict card was also rendering an H1. Fixed: page header H1 is now
  suppressed when a `verdict_state` is set; the verdict card owns the H1 for monograph
  posts.


All notable changes to the Peptide Starter Theme are documented in this file.

## [2.0.0-alpha.1] - 2026-04-26 — Brand Identity v1.0.0 (Direction C: Trusted Guide)

### Added
- Brand identity CSS token system in `assets/css/brand.css` (Direction C palette: Teal/Lime/Orange)
- Poppins + Inter + IBM Plex Mono font loading (Google Fonts) with preload/preconnect
- Full favicon/PWA/OG asset pack from peptide-repo-brand v1.0.0:
  - Logo variants: horizontal, horizontal-reverse, horizontal-on-teal, mark, mark-small, wordmark, wordmark-reverse, mono
  - Favicons: ICO + PNG 16/32/48, apple-touch-icon
  - PWA icons and manifest
  - og-default.png (1200×630) for social sharing fallback
- `inc/verdict-meta.php` — Post meta registration for pr_peptide CPT:
  - `verdict_state` (required): established|promising|investigational|insufficient|cautionary
  - `signal_row_1/2/3` (optional): evidence signal labels
  - Meta box in pr_peptide edit screen with sanitization and nonce protection
- Verdict badge component (`template-parts/verdict/badge.php`) with state-specific glyphs
- Verdict card component (`template-parts/verdict/card.php`) for monograph hero with evidence rows
- Evidence signal row sub-component (`template-parts/verdict/evidence-row.php`)
- Affiliate disclosure component (`template-parts/affiliate-disclosure.php`) with 3 contexts:
  - Inline (adjacent to links)
  - Banner (page-top disclosure)
  - Footer (persistent sitewide)
- `page-how-we-review.php` — How We Review Peptides page template with ToC sidebar
- `page-about.php` — About & Editorial Standards page template with disclosure block
- Footer "Our Method" link section pointing to verdict explainer and editorial standards
- Footer affiliate disclosure via template-part (footer context)
- OG image fallback meta tag in header.php (og-default.png)
- Favicon and PWA manifest links in header.php with theme-color
- SVG logo support in header via inline `logo-horizontal.svg` (fallback to text if unavailable)
- Skip-to-content link in header.php (screen reader text)
- Reduced-motion media query in brand.css for button transitions

### Changed
- `header.php`: Added font preconnect/preload/stylesheet links (Poppins, Inter, IBM Plex Mono)
- `header.php`: Swapped logo function to use inline SVG `logo-horizontal.svg` if available
- `functions.php`: Updated version to 2.0.0-alpha.1
- `functions.php`: Added `require_once` for new `inc/verdict-meta.php`
- `functions.php`: Enqueued `assets/css/brand.css` before main stylesheet (dependency order)
- `functions.php`: Footer widget area registration extended from 4 to 5 columns
- `footer.php`: Added "Our Method" footer section with two links
- `footer.php`: Integrated affiliate disclosure component (footer context)
- `inc/helpers.php`: Updated `peptide_starter_the_custom_logo()` to prefer SVG logo from `assets/brand/logo-horizontal.svg`

### Styling
- Brand CSS tokens cascade into existing theme; all new components use semantic token variables
- Verdict states (5-state taxonomy) signaled by color AND glyph AND label (never color-only)
- Lime (`#7FD600`) restricted to badge/button backgrounds with ink foreground (WCAG AA compliant)
- Prefers-reduced-motion honored for chip hover transitions
- All component BEM classes use `.pr-` prefix (brand) or `.ps-` prefix (theme); no collision with plugin prefixes (`.pn-`, `.psa-`, `.prab-`)

### Accessibility
- Verdict badge component: `role="img"` + `aria-label="Verdict: {State}"` for screen readers
- Glyph marked `aria-hidden="true"` to avoid redundant announcement
- Affiliate disclosure component: `<aside aria-label="Affiliate disclosure">` for context
- Skip-to-content link: `.screen-reader-text` class, keyboard-focusable
- All form inputs in verdict meta box properly labeled
- Evidence rows and card CTAs properly labeled and semantic

### Notes
- Requires brand assets to be copied into `assets/brand/`. All 9 SVG logos + 7 raster files included.
- Theme logo currently from hardcoded SVG; custom logo upload still supported via fallback.
- Verdict system is opt-in: card renders only if `verdict_state` meta is set; graceful fallback if not.
- Affiliate disclosure copy is hardcoded (not DB-driven) in v1. Can migrate to post meta or settings in v2.
- Footer extends to 5 columns; "Our Method" section can be replaced via widget for footer-5.
- Dark mode: verdict colors were validated for light mode only. Dark mode support planned for v1.1.

## [1.7.0] - 2026-04-24 — Remove WooCommerce integration

## [1.7.0] - 2026-04-24 — Remove WooCommerce integration

WooCommerce is not used on peptiderepo.com. Removing dead code reduces the asset
dequeue surface and eliminates the function_exists() guard introduced in the 2026-04-23
hotfix.

### Removed
- WooCommerce asset dequeue block from `inc/perf-asset-policy.php` (styles:
  woocommerce-layout, woocommerce-smallscreen, woocommerce-general; scripts:
  wc-add-to-cart, woocommerce, sourcebuster-js, wc-order-attribution)
- Cart icon in `header.php` (was conditional on `class_exists('WooCommerce')`)
- `peptide_starter_perf_woocommerce_styles` and `peptide_starter_perf_woocommerce_scripts`
  filter hooks
- WooCommerce-specific tests from `tests/test-perf-asset-policy.php`
- WooCommerce section from README.md and CONVENTIONS.md

## [1.6.0] - 2026-04-23 — Mobile Performance Phase 1

Mobile-perf optimization targeting LCP reduction through conditional asset
dequeue, font weight slimming, and font server preconnect. Measured baseline
LCP on Moto G Power (Slow 4G): 5.4s; target after Phase 1: ~2.5s.

### Added

- `inc/perf-asset-policy.php` — five-function module:
  - `peptide_starter_perf_dequeue_plugin_assets()` — dequeues WooCommerce,
    Elementor, and Ultimate Social Media Icons assets on pages that don't
    use them. Hook: `wp_enqueue_scripts` priority 100.
  - `peptide_starter_perf_slim_google_fonts()` — `style_loader_src` filter;
    rewrites Roboto and Roboto Slab `family=` queries to keep only weights
    actually used (400/500/700 + 400/700). Drops 72 font faces to 5.
  - `peptide_starter_perf_resource_hints()` — `wp_resource_hints` filter;
    adds preconnect hints for `fonts.googleapis.com` and `fonts.gstatic.com`.
  - `peptide_starter_perf_defer_cookie_notice()` — `script_loader_tag` filter;
    appends `defer` attribute to the cookie-notice front-end script.
  - `peptide_starter_page_uses_elementor( int $post_id )` — helper.
- Kill-switch constant `PEPTIDE_STARTER_PERF_DEQUEUE` (default `true`).
  Define `false` in `wp-config.php` to disable all dequeues instantly.
- Filterable handle lists: `peptide_starter_perf_woocommerce_handles`,
  `peptide_starter_perf_elementor_handles`, `peptide_starter_perf_usmi_handles`,
  `peptide_starter_perf_font_weights`.
- `tests/test-perf-asset-policy.php` — 13 unit-test cases covering kill-switch,
  per-page-context dequeue logic, font slim happy/edge cases, filter overrides,
  preconnect insertion, defer attribute application.

### Changed

- `style.css` Version header bumped 1.5.2 → 1.6.0.
- `PEPTIDE_STARTER_VERSION` constant bumped to `1.6.0`.

### Why

PageSpeed Insights mobile score on `https://peptiderepo.com/` was 74 with
LCP 5.4s, FCP 2.6s. Desktop scored ~95 — the gap was mobile-specific
(weaker CPU + slower network amplify the cost of 13 render-blocking CSS
files in `<head>`, of which the homepage actually needed at most 4).
Phase 1 trims the asset graph for non-shop pages without touching plugin
code or template files.

## [1.5.2] - 2026-04-14 — Security

Same-day follow-up to v1.5.1. Closes four issues the v1.5.1 post-merge
review flagged — two ship-blockers (PSEC-007 CF header spoof, PSEC-008
synchronous migration) and two mediums (PSEC-009 timing oracle,
PSEC-010 test-coverage gap). All fixes stay within ADR-0001's decision
envelope — no new ADR required.

### Security fixes

- **PSEC-007** — `peptide_starter_get_client_ip()` now only trusts
  `HTTP_CF_CONNECTING_IP` when `REMOTE_ADDR` itself is inside a
  published Cloudflare edge range. Direct-to-origin requests (which
  Hostinger allows) can no longer forge their source IP by sending a
  `CF-Connecting-IP` header. `HTTP_X_FORWARDED_FOR` is ignored unless
  explicitly opted in via the `peptide_starter_trust_xff` filter. New
  module `inc/cloudflare-ips.php` holds the range snapshot (dated
  2026-04-14) + a CIDR matcher for both IPv4 and IPv6. Override the
  list via the `peptide_starter_cloudflare_ip_ranges` filter.
- **PSEC-008** — Removed the v1.5.1 re-verification migration entirely
  (Option A). Existing subscribers are now grandfathered — treated as
  verified. Verification applies to accounts registered from v1.5.2
  onward. The `admin_init`-driven synchronous `wp_mail` loop that blocked
  wp-admin is gone; its `ps_verify_migration_version` option is cleared
  on theme activation.
- **PSEC-009** — Registration validation no longer short-circuits.
  Pattern / email / password / `username_exists` / `email_exists` all
  evaluate unconditionally before the combined rejection, closing the
  response-time enumeration oracle that distinguished microsecond-fast
  regex rejection from DB-round-trip rejection.

### Tests

- `test-rate-limiter.php` — seven new cases covering CF-peer trust,
  spoofed-header rejection, invalid-value fallthrough, XFF default-off
  behaviour, XFF filter opt-in, filter override of the CF range list,
  IPv6 CIDR matching, and family-mismatch rejection.
- `test-auth-handlers.php` — `test_register_runs_all_checks_even_when_early_one_fails`
  asserts DB-backed user lookups still execute when a cheap validation
  check has already failed (PSEC-009).
- `test-email-verification.php` — `test_valid_token_verifies_user_clears_meta_and_fires_hook`
  covers the happy-path verify flow end-to-end including hook firing
  (PSEC-010).

### Documentation

- `ARCHITECTURE.md` — CF IP trust model explained in the Security
  Summary; migration decision (grandfather) documented.
- `CONVENTIONS.md` — new "Trust model for request headers" note.

### Rollback note

If v1.5.2 causes login or registration failures, roll back to v1.5.0
(not v1.5.1 — v1.5.1 carried the unresolved PSEC-007 hole and the
blocking migration). Rate-limit transients auto-expire.

---

## [1.5.1] - 2026-04-14 — Security

Security-only release hardening the frontend authentication surface introduced
in v1.5.0. Governed by ADR-0001 (Frontend authentication and abuse-control
strategy). No breaking changes to public routes, AJAX action names, or nonce
action strings.

### Security fixes

- **PSEC-001** — Rate limiting on login (5/min per IP+email), registration
  (3/hour per IP), contact (5/hour per IP), newsletter (3/hour per IP), and
  verification resend (2/hour per IP+email). Transient-backed, auto-expiring.
  Storage never holds raw IPs — keys are truncated `wp_hash` digests.
- **PSEC-002** — Unified error messages on login and registration. Removed
  separate "username exists" / "email exists" / "user not found" branches
  that enabled account enumeration via the error-message oracle. All login
  failures now return `Invalid email or password.`; all registration
  validation failures return `Unable to create account. Please check your
  entries and try again.`
- **PSEC-003** — Removed auto-login from the registration handler. New
  registrations now go through an email verification flow: a 43-char random
  token (stored in user meta with a 24h TTL) is emailed via `wp_mail` and
  must be clicked to activate the account. Verification route is `/verify`.
  `wp_mail` deliverability can be validated any time via the new admin tool
  at Tools → Mail Test.
- **PSEC-004** — `peptide_starter_require_login()` gate applied at the top
  of `page-subject-log.php`, `page-tracker.php`, `page-protocol-builder.php`.
  Unauthenticated users bounce to `/auth?redirect_to=…`; unverified users
  bounce to `/profile?verify_required=1` which surfaces a resend button.
  `page-calculator.php` stays open — it holds no PII and does no writes.
- **PSEC-005** — CSV injection fix in the newsletter admin export. Every
  cell passed to `fputcsv` routes through `peptide_starter_csv_safe()`,
  which prefixes values starting with `=`, `+`, `-`, `@`, `\t`, `\r` with
  a single quote so spreadsheet apps won't interpret them as formulas.
- **PSEC-006** — Honeypot fields on all four public forms (login, register,
  contact, newsletter). Any non-empty value fakes a success response and
  drops the submission. No PII is ever logged — only a sha256-truncated
  IP hash so admins can correlate if they need to.

### Added

- `inc/config.php` — single source of truth for every security threshold;
  filterable via `peptide_starter_security_config`.
- `inc/rate-limiter.php` — `Peptide_Starter_Rate_Limiter` class with
  `check`/`record`/`reset` lifecycle + `peptide_starter_get_client_ip()`
  helper (Cloudflare-aware).
- `inc/email-verification.php` — token generation, verification route
  handler, user-verified check, and rate-limited AJAX resend endpoint
  (`ps_resend_verify`).
- `inc/mail-diagnostic.php` — permanent admin tool under Tools → Mail Test
  that exercises `wp_mail` and surfaces duration + PHPMailer error.
- v1.5.0 user migration in `inc/page-setup.php`: existing subscribers are
  enrolled into the email verification flow on first admin load after the
  upgrade (guarded by `ps_verify_migration_version` option).
- Newsletter consent checkbox + privacy policy link on the signup form.
- Newsletter unsubscribe flow: every subscription now stores a token;
  `/newsletter-unsubscribe?token=…` flips the `unsubscribed` flag without
  requiring a login.
- Profile page verify-required banner with resend button (AJAX).
- `peptide_starter_render_honeypot()` and `peptide_starter_csv_safe()`
  helpers in `inc/helpers.php`.
- PHPUnit suites: `test-rate-limiter.php`, `test-auth-handlers.php`,
  `test-email-verification.php`, `test-contact-handler.php`,
  `test-newsletter.php`, `test-auth-gate.php`.

### Changed

- `functions.php` loads the five new modules in dependency order.
- Newsletter handler stores entries with `autoload=false` so a large
  subscriber list no longer bloats every page load. Admin notice surfaces
  once subscribers exceed 1000 (threshold is configurable).
- Newsletter handler collapses `success` and `duplicate` response states
  into a single `ok` state — no longer leaks subscription status.
- Contact handler rejects sender names containing `\r`, `\n`, `,`, `<`,
  `>` up front (header-injection defence) and tags mail failures with a
  request ID so admins can correlate without storing message content.
- Fallback primary-menu function moved from `header.php` into
  `inc/helpers.php` to eliminate redeclaration risk.
- Footer placeholder language links (`href="#"`) replaced with a
  "Translations coming soon" note. Will render localized links once
  per-language blogs exist.
- Footer copyright now renders through `peptide_starter_get_footer_copyright()`
  so the customizer control is live (was dead before).
- Settings panel now implements a proper Tab / Shift+Tab focus trap and
  saves + restores `document.body.style.overflow` so nested overlays
  don't corrupt scroll state.
- Documentation heading IDs are slugified from heading text with a
  collision counter so deep links survive reorders.
- `assets/js/auth.js`, `settings-panel.js`, `documentation.js`,
  `theme.js`, `navigation.js` standardized on `const` / `let`.

### Removed

- Auto-login on registration (see PSEC-003).
- Placeholder-link spam in the footer (see Changed).

### Documentation

- `ARCHITECTURE.md` updated with the new `inc/` module list, authentication
  data flow, security abuse-control overview, and verification route.
- `CONVENTIONS.md` documents the rate-limiter usage pattern, the honeypot
  pattern, the `require_login` gate, and CSV-safe export.
- ADR-0001 (Frontend authentication and abuse-control strategy) governs
  the trade-offs behind this release.

### Known follow-ups (out of scope; tracked as future ADRs)

- ADR-0003 — consolidate the six duplicated tool templates.
- ADR-0004 — migrate newsletter storage from `wp_options` to a dedicated
  table once subscriber count exceeds ~1000.

---

## [1.5.0] - 2026-04-14

Theme extension release: navigation dropdowns, 9 page templates, branded
frontend auth UI, newsletter signup, contact/support panel, footer update,
and Research Modules cards on the front page. Superseded by v1.5.1 on the
same day — v1.5.0 shipped with the auth-surface issues captured in
ADR-0001.

---

## [1.3.2] - 2026-04-12

### Security
- **Newsletter form nonce:** Added `wp_nonce_field()` and server-side nonce verification to the footer newsletter signup form (CSRF protection)
- **Newsletter form handler:** Added `peptide_starter_handle_newsletter_signup()` via `admin_post` action with email sanitization and validation
- **Customizer sanitize callbacks:** Added `sanitize_callback` to all five Customizer settings (hero_title, hero_subtitle, hero_search_placeholder, footer_copyright, dark_mode_default) — prevents unsanitized input from being saved
- **Checkbox sanitizer:** Added `peptide_starter_sanitize_checkbox()` helper for boolean Customizer values

### Added
- **PHPCS in CI:** WordPress-Core coding standard check added to deploy pipeline (blocks deploy on violations)
- **JS syntax check in CI:** `node --check` validation on all JS files in `assets/js/`
- **PHPUnit test scaffold:** Bootstrap, config, and initial test suites covering theme helpers, setup, and sanitizers
- **Composer dev dependencies:** PHPUnit 9, PHPCS 3, WPCS 3 for local development
- **PHPCS config:** `.phpcs.xml.dist` with WordPress-Core standard, text domain, and PHP/WP version constraints

### Changed
- **Deploy safety:** Added excludes for dev-only files in rsync (tests/, vendor/, composer.json, phpunit.xml, .phpcs.xml.dist) — prevents dev tooling from reaching production
- **CI job name:** Renamed from "Validate PHP Syntax" to "Validate PHP & JS" to reflect expanded checks

### Documentation
- Updated ARCHITECTURE.md with testing infrastructure, security details, and CI/CD pipeline description
- Updated CHANGELOG.md with this release

---

## [1.3.1] - 2026-04-10

### Added
- **PRAutoBlogger Integration:** Added `[prautoblogger_posts]` shortcode widget to front page
  - Displays 6 auto-generated research articles in "Latest Research & Insights" section
  - Appears between hero section and Peptide News feed
  - Gracefully hides if PRAutoBlogger plugin is not active
  - Uses `.prab-*` namespace for plugin-specific styling

### Changed
- **Deploy Mechanism:** Switched from GitHub Actions direct build to rsync SSH deployment
  - No longer requires build step or plugin compilation
  - Files deployed directly from repository to Hostinger via rsync over SSH
  - LiteSpeed cache purged automatically after deploy
  - Theme path on server: `~/domains/peptiderepo.com/public_html/wp-content/themes/peptide-starter/`
  - Simpler CI/CD pipeline; no external build dependencies

### Technical Details
- PRAutoBlogger integration uses `shortcode_exists()` for graceful degradation
- Deploy workflow (`deploy.yml`) now runs rsync directly without intermediate steps
- Maintains backward compatibility with all existing features

---

## [1.3.0] - 2026-04-10

### Added
- **Dark Mode Toggle:** Full dark mode support with persistent user preference
  - Toggle button in header (sun/moon icons)
  - Preference saved to localStorage as `peptide-starter-theme`
  - Respects system `prefers-color-scheme` preference
  - Customizer option to set dark mode as default on first visit
  - Inline script in `wp_head` prevents white flash on page load
  - Custom `themechange` event dispatched to plugins for dark mode coordination

- **Plugin Coordination System:** Dark mode and theme state broadcasting
  - Custom `themechange` DOM event with `{ theme: 'dark' | 'light' }` payload
  - `html[data-theme]` attribute for CSS selectors
  - `window.peptideStarterTheme` API for plugin access (getCurrentTheme, setTheme, toggleDarkMode)
  - CSS custom properties swap automatically on dark mode toggle

- **Full Plugin Integration Suite:**
  - Peptide Search AI shortcode: `[peptide_search]` in hero section
  - Peptide News shortcode: `[peptide_news]` on front page
  - All plugins respect theme CSS variables and dark mode events
  - Graceful fallback for missing plugins (search form, empty sections)

- **Customizer Settings:**
  - Hero title, subtitle, search placeholder
  - Footer copyright text
  - Dark mode default toggle

- **4-Column Footer Widget Areas:**
  - Registered: footer-1, footer-2, footer-3, footer-4
  - Default fallback content per column (About, Links, Resources, Connect)

- **Template Hierarchy:**
  - Front page with hero + plugin widgets
  - Generic page template with headers
  - CPT support: single-peptide.php, archive-peptide.php
  - 404 error page
  - Search template with fallback

- **Accessibility (WCAG AA):**
  - Skip-to-main-content link
  - Proper heading hierarchy (H1-H6)
  - Focus indicators (2px outline) on all interactive elements
  - 4.5:1+ contrast ratio on all text
  - Semantic HTML with proper landmarks
  - `aria-label` and `aria-expanded` attributes on buttons
  - Keyboard navigation support (Escape closes overlays, Tab through elements)

- **Responsive Breakpoints:**
  - Mobile: 320px - 479px
  - Tablet: 480px - 767px
  - Desktop: 768px - 1023px
  - Large: 1024px - 1439px
  - Extra Large: 1440px+

- **Design System:**
  - Typography: Inter font with 1.125x modular scale, 8px grid base
  - Colors: Blue primary (#0066CC light, #3B82F6 dark)
  - Component library: buttons, cards, forms, badges, alerts, pagination
  - CSS custom properties for all design tokens
  - No `!important` on global elements (plugin harmony)

- **Navigation & Mobile Menu:**
  - Primary navigation with active state detection
  - Mobile hamburger menu with overlay
  - Custom Nav Walker for WordPress theme integration
  - Keyboard navigation (Escape closes menu, arrow keys work)
  - Responsive: hides on desktop, appears on mobile (< 768px)

### Technical Details
- **Architecture:** Classic WordPress theme (non-block/FSE)
- **Styling:** Single 44KB `style.css` with no build step
- **JavaScript:** Vanilla ES6+ in 2 files (navigation.js, theme.js)
- **Plugin Namespaces:** `.pn-*` (Peptide News), `.psa-*` (Peptide Search AI), `.prab-*` (PRAutoBlogger)
- **Browser Support:** Chrome, Firefox, Safari (latest 2), iOS Safari 12+
- **Requires:** WordPress 6.0+, PHP 7.4+

### Initial Release
- All core features implemented and tested
- Full dark mode support with ecosystem coordination
- Production-ready for peptiderepo.com launch

---

## Version Numbering

- Versions follow semantic versioning: MAJOR.MINOR.PATCH
- Current: v1.3.2
- Defined in: `style.css` header (line 7) and `functions.php` (line 14 constant)
- Update both locations when releasing a new version

---

## Deployment

Push to `main` branch triggers automatic deployment to peptiderepo.com via GitHub Actions.

**Process:**
1. Push to `main`
2. GitHub Actions workflow `deploy.yml` runs
3. Files synced via rsync to Hostinger
4. LiteSpeed cache purged

No manual FTP uploads needed.
