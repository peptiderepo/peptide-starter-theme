# Peptide Starter Theme

WordPress theme for peptiderepo.com — a scientific peptide reference database.

## Repository

- **GitHub**: `peptiderepo/peptide-starter-theme` (private)
- **Branch**: `main` is the production branch
- **Live site**: https://peptiderepo.com
- **Hosting**: Hostinger shared hosting

## Deploy

Push to `main` triggers GitHub Actions → rsync via SSH to Hostinger.
No CI lint step — the deploy workflow (`deploy.yml`) runs rsync directly.
LiteSpeed cache is purged automatically after deploy.

Theme path on server: `~/domains/peptiderepo.com/public_html/wp-content/themes/peptide-starter/`

## Architecture

This is a classic WordPress theme (no block/FSE). All styling is in `style.css` (no build step, no preprocessor). JavaScript is vanilla — no bundler.

### Key files

| File | Purpose |
|------|---------|
| `style.css` | All theme CSS (~44KB). Organized: reset → tokens → typography → globals → components → regions → pages → animations → a11y → dark mode → responsive |
| `functions.php` | Theme setup, script enqueueing, Customizer registration, nav walker, helper functions |
| `front-page.php` | Home page: hero section → PRAutoBlogger widget → Peptide News feed |
| `header.php` | Global header with nav, dark mode toggle, mobile menu |
| `footer.php` | 4-column widget footer with copyright |
| `archive-peptide.php` | Peptide CPT archive grid |
| `single-peptide.php` | Single peptide detail page |
| `assets/js/navigation.js` | Mobile menu and nav functionality |
| `assets/js/theme.js` | Dark mode toggle, theme utilities |

### Template parts

- `template-parts/content.php` — Post/page content loop
- `template-parts/content-peptide.php` — Peptide card in archive
- `template-parts/content-none.php` — Empty state

## Coding Conventions

- **PHP**: WordPress coding standards — tabs for indentation, Yoda conditions, braces on same line
- **CSS**: No preprocessor. Use CSS custom properties (defined in `:root`). All theme classes prefixed `.ps-`
- **JS**: Vanilla ES6+, no build step. Files in `assets/js/` must pass `node --check`
- **No `!important`** on global elements — prevents plugin conflicts
- Plugin CSS namespaces are untouched (`.pn-*` for Peptide News, `.psa-*` for Peptide Search AI, `.prab-*` for PRAutoBlogger)

## Design System

- **Font**: Inter (Google Fonts) with system fallback
- **Scale**: 1.125x modular, 8px grid base
- **Primary color**: `#0066CC` (light) / `#3B82F6` (dark)
- **Breakpoints**: 480px, 768px, 1024px, 1440px (mobile-first)
- **Dark mode**: CSS custom properties swap via `html[data-theme="dark"]` or `prefers-color-scheme: dark`

## Plugin Integration

The theme integrates with three companion plugins via shortcodes, all wrapped in `shortcode_exists()` for graceful degradation:

1. **Peptide Search AI** (`[peptide_search]`) — AI search in hero section
2. **Peptide News** (`[peptide_news]`) — News feed on front page
3. **PRAutoBlogger** (`[prautoblogger_posts]`) — Auto-generated research articles on front page

## Customizer Options

Editable via Appearance → Customize:
- Hero title, subtitle, search placeholder
- Footer copyright text
- Dark mode default on/off

## Version

Current: **v1.3.1** (defined in `style.css` header and `PEPTIDE_STARTER_VERSION` constant in `functions.php`)

When bumping version, update both locations.
