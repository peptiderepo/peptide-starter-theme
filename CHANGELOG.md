# Peptide Starter Theme - Changelog

All notable changes to the Peptide Starter Theme are documented in this file.

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
