# Peptide Starter Theme - Changelog

## [2.2.1] - 2026-04-29 — RCT inline abbreviation tooltip

### Added

- `.pr-abbr` tap-target on the term "RCT" in the Established verdict card on
  the verdict legend strip (homepage). Tapping/clicking opens a small inline
  popover with the full definition: "Randomized Controlled Trial — the gold
  standard for clinical evidence." Closes on outside click, Escape key, or
  focus leaving the element.
- `assets/js/abbr-tooltip.js` — new JS module (~130 lines) for popover open/
  close, outside-click dismissal, keyboard accessibility (Enter/Space/Escape),
  and ARIA attributes (`aria-expanded`, `aria-describedby`, `role="tooltip"`).
  Enqueued on front page only (`is_front_page()`).
- `peptide_starter_render_desc( string $text, array $abbrs ): string` helper in
  `inc/verdict-helpers.php`. Builds safe HTML from `esc_html`/`esc_attr`
  components; verdict taxonomy items carry an optional `abbrs` map.
- `.pr-abbr` and `.pr-abbr-popover` CSS in `style.css` — dashed underline
  affordance, fade-in animation, dark-mode-safe surface variables.

### Fixed

- `PEPTIDE_STARTER_VERSION` constant in `functions.php` was stale at
  `2.0.0-alpha.3`; realigned to `2.2.1` to match `style.css` Version header.

## [2.1.8] - 2026-04-28 — Homepage verdict section spacing fix

### Fixed

- Added missing CSS for `hero-featured-verdicts`, `hero-verdict-card`, and
  `hero-verdict-explainer` BEM classes used in `front-page.php`. These classes
  were referenced in the template but had zero CSS rules, causing the Featured
  Verdicts section to render with bare browser defaults (no grid layout, no card
  padding, no visual separation between verdict entries).
- Added responsive 3-column grid for verdict cards at ≥900px breakpoint.
- Added 5-column grid for verdict explainer items at ≥900px, 3-column at ≥640px.
- Verdict card left-border now tints to match verdict state via CSS `:has()`.


All notable changes to the Peptide Starter Theme are documented in this file.

## [2.1.7] - 2026-04-27 — Newsletter signup infrastructure (Beehiiv)

### Added

- `page-subscribe.php` — single-column conversion template for `/subscribe/` landing page.
  No sticky ToC; signup-form-centric layout.
- `single-peptide.php` — compact Beehiiv signup block inserted after monograph article,
  before prev/next navigation. Copy: "Want updates on monographs like this? One email a
  week, no spam." Form ID: `6edcd482-cbea-4767-8777-97ac17d6d609`.
- `footer.php` column 1 — "Get our weekly digest →" link to `/subscribe/` below brand blurb.
- `functions.php` — `peptide_starter_enqueue_beehiiv_attribution()` loads Beehiiv's
  `attribution.js` site-wide (footer, async) for UTM source capture across all placements.

### Notes

- Beehiiv form ID and embed script URL are compile-time constants in the templates.
  If the form ID changes, update `single-peptide.php` and `page-subscribe.php`.
- `single-repo_daily.php` and `archive-repo_daily.php` signup placements deferred to
  the Repo Daily sprint (templates don't exist yet).
- Sender: `repo-weekly@mail.peptiderepo.com`. DNS records (SPF/DKIM/DMARC for
  `mail.peptiderepo.com`) to be added by Terence in Cloudflare when Beehiiv
  sending-domain verification runs.

## [2.1.6] - 2026-04-27 — Our Methodology page template + footer link

### Added

- `page-our-methodology.php` — two-column ToC template for the `/our-methodology/`
  editorial-standards page. Mirrors `page-how-we-review.php` layout; CSS classes
  use `page-our-methodology` prefix. The `#last-verified` anchor on this page is the
  deep-link target for Phase 2 verdict-card "Last verified" lines.
- Footer "Our Method" section now includes **Our Methodology** link (`/our-methodology/`)
  alongside How We Review Peptides and Editorial Standards.

### Notes

- No changes to existing templates, CSS, or JS.
- `footer.php` also backports the section-5 "Our Method" block from the v2.1.5 brand
  sprint which was live on the server but not committed to the repo.

## [1.6.0] - 2026-04-23 — Mobile Performance Phase 1

Mobile optimization targeting LCP reduction through conditional asset dequeue,
font weight slimming, and font server preconnect. Measured baseline LCP on
Moto G Power (Slow 4G): 5.4s; target: sub-4s after Phase 1 deployment.

### Performance improvements

- **Conditional asset dequeue**: WooCommerce, Elementor, and USMI assets now
  dequeue on pages that don't use them. Removes ~100KB from non-shop pages.
- **Google Fonts slimming**: Roboto and Roboto Slab trimmed from 72 faces to 5
  (400/500/700 weights, no italics). Font CSS shrinks ~6KB → <1KB; woff2 payload
  reduced ~80%.
- **Preconnect hints**: `fonts.googleapis.com` and `fonts.gstatic.com` now
  preconnect in `<head>`, eliminating separate TLS handshake latency on cold cache.
- **Cookie-notice defer**: Cookie banner script deferred to unblock initial render.

### Technical

- New module `inc/perf-asset-policy.php` (~245 lines, PHPUnit tested).
- Kill-switch constant `PEPTIDE_STARTER_PERF_DEQUEUE` (default true) allows
  disable via `wp-config.php` for rollback.
- All dequeue lists and font weights exposed as filters for customization.
- Production handle verification: WC (layout/smallscreen/general), Elementor
  (frontend/frontend-legacy + per-post CSS), USMI (SFSImainCss + 4 scripts).

### Tests

- `test-perf-asset-policy.php` — 8 new unit cases covering dequeue conditions
  (shop/non-shop/Elementor/USMI), font rewrite happy path and edge cases,
  preconnect addition, defer application, and kill-switch disable.

### Out of scope

- Critical CSS inline (Phase 2 candidate after measuring Phase 1 lift).
- jQuery dequeue (WC/PSA/Elementor compat risk too high).
- Image optimizations (no images on homepage critical path).
- LiteSpeed Cache settings (CTO tunes post-deploy if Phase 1 insufficient).

### Breaking changes

None. All optimizations are transparent to plugin authors; dequeues use
standard WordPress APIs and can be overridden via filters or re-enqueued by
plugins if needed.

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
