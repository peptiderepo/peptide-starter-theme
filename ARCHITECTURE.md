# Peptide Starter Theme - Architecture

## Overview

Peptide Starter (peptide-starter) is a premium, accessible WordPress theme for peptiderepo.com — a scientific peptide reference database. The theme is a classic (non-block) WordPress theme that assembles a modern, responsive frontend by combining a monolithic CSS architecture with vanilla JavaScript. It integrates seamlessly with three companion plugins (Peptide Search AI, Peptide News, PRAutoBlogger) via shortcodes, coordinates dark mode across the ecosystem via CSS custom properties and the DOM `data-theme` attribute, and provides customizable hero and footer sections through the WordPress Customizer.

## Directory Structure

```
peptide-starter/
├── style.css                    # All theme CSS (~44KB). Architecture: reset → tokens → typography → 
│                                 # globals → components → regions → pages → animations → a11y → dark mode → responsive
│
├── functions.php                # Theme setup (430 lines)
│                                 # - Theme support registration
│                                 # - Script/style enqueueing with wp_localize_script
│                                 # - 4x footer widget area registration
│                                 # - Customizer settings (hero, footer, dark mode)
│                                 # - Navigation Menu Walker for active states
│                                 # - Helper functions (get_hero_title, get_footer_copyright, etc.)
│                                 # - Inline dark mode script in wp_head (for flicker-free initialization)
│
├── header.php                   # Global header region (131 lines)
│                                 # - Site logo/branding
│                                 # - Primary navigation menu with fallback
│                                 # - Header icons (search, dark mode toggle, cart, mobile menu)
│                                 # - Search overlay for Peptide Search AI plugin
│                                 # - Nav overlay for mobile menu
│
├── footer.php                   # Global footer region (81 lines)
│                                 # - Newsletter signup form (conditionally rendered)
│                                 # - 4-column footer widget grid
│                                 # - Default content fallback per column
│                                 # - Footer copyright text (from Customizer)
│
├── front-page.php               # Home page template (86 lines)
│                                 # - Hero section (title, subtitle, search)
│                                 # - PRAutoBlogger posts widget [prautoblogger_posts]
│                                 # - Peptide News feed section [peptide_news]
│                                 # - Shortcode integration with graceful degradation
│
├── page.php                     # Generic page template (53 lines)
│                                 # - Page header with title
│                                 # - Article container
│                                 # - Edit link display
│
├── index.php                    # Fallback template (27 lines)
│                                 # - Loop with dynamic post type content dispatch
│                                 # - Pagination via peptide_starter_pagination()
│
├── single-peptide.php           # Peptide detail page (CPT)
│                                 # - Renders single peptide post
│
├── archive-peptide.php          # Peptide archive (CPT grid)
│                                 # - Displays peptide CPT items in grid layout
│
├── 404.php                      # 404 error page
│                                 # - Default 404 handling
│
├── searchform.php               # Search form template
│                                 # - Standard WordPress search form
│
├── template-parts/
│   ├── content.php              # Default post/page loop item (19 lines)
│                                 # - Title, date, author, excerpt, read more link
│
│   ├── content-peptide.php      # Peptide CPT card template
│                                 # - Peptide-specific card styling with .ps-card
│
│   └── content-none.php         # Empty state template
│                                 # - Message when no posts found
│
├── assets/
│   └── js/
│       ├── navigation.js        # Mobile menu toggle (85 lines)
│                                 # - Mobile hamburger menu state management
│                                 # - Nav overlay click handling
│                                 # - Escape key, resize, link click closing
│                                 # - aria-expanded attribute updates
│
│       └── theme.js             # Dark mode + utilities (211 lines)
│                                 # - Dark mode toggle & localStorage persistence
│                                 # - System preference detection (prefers-color-scheme)
│                                 # - Custom 'themechange' event dispatch to plugins
│                                 # - Search overlay open/close functionality
│                                 # - Icon state toggling (sun/moon)
│                                 # - window.peptideStarterTheme API for plugin access
│                                 # - Smooth scroll + focus-visible polyfill
│
├── languages/                   # Translation files (.pot, .po, .mo)
│                                 # - Text domain: peptide-starter
│
├── tests/
│   ├── bootstrap.php            # PHPUnit bootstrap — loads WP test framework + theme
│   ├── test-functions.php       # Tests for helper functions (hero, footer, newsletter, sanitize)
│   └── test-theme-setup.php     # Tests for theme supports, menus, widgets, scripts
│
├── .phpcs.xml.dist              # PHPCS config — WordPress-Core standard
├── phpunit.xml                  # PHPUnit config — points to tests/ directory
├── composer.json                # Dev dependencies (PHPUnit, PHPCS, WPCS)
├── README.md                    # User-facing documentation
├── CLAUDE.md                    # Developer context notes
├── CHANGELOG.md                 # Version history
└── screenshot.png               # WordPress theme preview image
```

## Data Flow & Architecture Diagrams

### Front Page Assembly (Plugin Integration)

```
front-page.php
    │
    ├─→ Hero Section (title, subtitle, search)
    │   │
    │   └─→ [peptide_search] shortcode
    │       └─→ Peptide Search AI plugin (if active)
    │
    ├─→ Hero CTAs (Browse Peptides, Learn More)
    │
    ├─→ [prautoblogger_posts] shortcode
    │   └─→ PRAutoBlogger plugin
    │       └─→ Renders 6 auto-generated research articles
    │
    └─→ [peptide_news] shortcode
        └─→ Peptide News plugin
            └─→ Renders news feed grid
```

### Dark Mode Coordination

```
functions.php (wp_head)
    │
    └─→ Inline script runs first (before CSS loads)
        └─→ Sets html[data-theme] from:
            1. localStorage (user's choice)
            2. prefers-color-scheme (system)
            3. dark_mode_default (Customizer setting)
        └─→ Prevents flash of wrong color on page load
                │
                ├─→ style.css reads data-theme
                │   └─→ @media selector: html[data-theme="dark"]
                │
                └─→ theme.js detects theme changes
                    └─→ Dispatches 'themechange' event
                    └─→ Updates localStorage
                    └─→ Updates icon visibility
                    └─→ Plugins listen & update own styles
```

### Template Hierarchy & Routing

```
WordPress Request
    │
    ├─→ is_front_page() ? front-page.php
    │   └─→ Hero + [prautoblogger_posts] + [peptide_news]
    │
    ├─→ is_singular('peptide') ? single-peptide.php
    │   └─→ get_template_part('content-peptide')
    │
    ├─→ is_archive() ? archive-peptide.php
    │   └─→ Grid of peptide cards
    │
    ├─→ is_page() ? page.php
    │   └─→ get_template_part('content')
    │
    ├─→ is_search() || is_category() || is_tag() ? index.php
    │   └─→ Loop: get_template_part('content', get_post_type())
    │       └─→ Dispatches to content.php or content-{type}.php
    │
    ├─→ is_404() ? 404.php
    │
    └─→ Default ? index.php
        └─→ Fallback loop template
```

### Script Loading & Dark Mode API

```
wp_enqueue_scripts (functions.php)
    │
    ├─→ peptide-starter-style
    │   └─→ style.css (PEPTIDE_STARTER_VERSION)
    │
    ├─→ peptide-starter-navigation
    │   └─→ assets/js/navigation.js (in footer, async)
    │
    └─→ peptide-starter-theme
        ├─→ assets/js/theme.js (in footer, async)
        └─→ wp_localize_script('peptideStarterData')
            ├─→ siteUrl: home_url('/')
            └─→ isDarkMode: get_theme_mod('dark_mode_default')
```

### Plugin Dark Mode Listening

Plugins can coordinate with the theme via three mechanisms:

```javascript
// 1. Custom event (recommended)
document.addEventListener('themechange', (e) => {
  console.log('Theme changed to:', e.detail.theme); // 'dark' | 'light'
});

// 2. DOM attribute mutation
const observer = new MutationObserver(() => {
  const theme = document.documentElement.getAttribute('data-theme');
});
observer.observe(document.documentElement, { attributes: true });

// 3. CSS custom properties (theme.js updates these)
const color = getComputedStyle(document.documentElement).getPropertyValue('--text-primary');
```

## External Integrations

### Plugin Integrations (via Shortcodes)

1. **Peptide Search AI** (`[peptide_search]`)
   - Location: Hero section (header.php, front-page.php)
   - Fallback: Standard WordPress search form
   - Dark mode: Listens to `themechange` event
   - Namespace: `.psa-*`

2. **Peptide News** (`[peptide_news]`)
   - Location: Front page (front-page.php)
   - Renders: News feed grid with article cards
   - Dark mode: Listens to `themechange` event
   - Namespace: `.pn-*`
   - Override: `functions.php` line 80-90 removes `-webkit-line-clamp` on excerpts

3. **PRAutoBlogger** (`[prautoblogger_posts]`)
   - Location: Front page (front-page.php)
   - Renders: 6 auto-generated research articles section
   - Dark mode: Listens to `themechange` event
   - Namespace: `.prab-*`

All plugins are checked with `shortcode_exists()` for graceful degradation.

### WooCommerce Integration

- Cart icon in header (header.php line 68)
- Conditionally rendered if `class_exists('WooCommerce')`
- Minimal theme styling; WooCommerce handles its own UI
- Icon: SVG shopping cart

### Google Fonts Integration

- Font: **Inter** (from Google Fonts)
- Loaded via CSS `@import url('...')`
- Fallback: System fonts (`-apple-system, BlinkMacSystemFont, etc.`)
- Display: `display=swap` for optimal loading

### REST API & Customizer

- Theme mods saved to `wp_options` table
- Customizer sections: Branding, Hero Section, Footer, Theme Mode
- All settings use `'transport' => 'postMessage'` for instant preview (except dark_mode_default which uses refresh)

## Key Architectural Decisions

### 1. Classic Theme (Not FSE/Block Theme)

**Why:** Peptide Repo predates WordPress Full Site Editing. Classic themes provide:
- Simpler, more stable template hierarchy for developers
- Full control over HTML structure without block markup
- Easier plugin compatibility without block interdependencies
- Proven stability for production scientific database

### 2. Monolithic CSS (Not SASS/LESS)

**Why:**
- No build step = instant deploy without compilation
- Single 44KB file loads fast (gzips to ~7KB)
- CSS custom properties (variables) provide all needed abstraction
- Theme can be edited directly on remote server if needed
- Reduces deployment complexity on Hostinger shared hosting

### 3. Vanilla JavaScript (No Bundler)

**Why:**
- Simple, maintainable code without build toolchain
- Direct browser compatibility (ES6+)
- Minimal overhead; only 296 lines total across 2 files
- Each script can be tested by running `node --check` syntax validation
- No dependencies = no supply chain risk

### 4. Shortcode Integration (Not Direct Function Calls)

**Why:**
- Plugins are independent; shortcodes decouple content from theme
- Shortcodes can be disabled/removed without theme breaking
- `shortcode_exists()` provides graceful degradation
- Theme doesn't need to know plugin implementation details
- Content (front-page.php) stays separate from plugin business logic

### 5. CSS Custom Properties for Dark Mode & Theming

**Why:**
- Single source of truth for all colors (defined in `:root`)
- Plugins can read theme colors via `getComputedStyle()`
- Easy to switch between light/dark without duplicate selectors
- No need for SASS mixins or preprocessor
- Future-proof: standard CSS, works in all modern browsers

### 6. No `!important` Policy (Global Scale)

**Why:**
- `!important` breaks plugin CSS specificity
- Forces explicit specificity through selector weight instead
- Theme respects plugin namespaces (`.pn-*`, `.psa-*`, `.prab-*`)
- Only exception: line 85 (necessary override of Peptide News line-clamp)
- Maintains CSS maintainability and plugin harmony

### 7. Inline Dark Mode Script in `wp_head`

**Why:**
- Runs *before* CSS loads, preventing white flash in dark mode
- Reads localStorage synchronously (no async needed here)
- Sets `data-theme` attribute before first paint
- Fallback chain: localStorage → system preference → customizer default
- Performance impact: minimal (~1ms execution)

### 8. Navigation Walker with Active State Detection

**Why:**
- Custom Walker class (line 243-300) detects current-menu-item
- Adds `.active` class for CSS styling (matches WordPress conventions)
- Enables dynamic nav styling without JS
- Integrates with WordPress menus natively
- Fallback function for sites with no primary menu

### 9. Helper Functions for Customizer Output

**Why:**
- `peptide_starter_get_*()` functions (lines 317-340) centralize theme mod retrieval
- Consistent defaults across templates
- Easy to update copy site-wide from one place
- Separates data layer from presentation

## Security & Performance Considerations

- **Escaping:** All output uses `esc_html()`, `esc_url()`, `esc_attr()` appropriately
- **Nonces:** Newsletter form uses `wp_nonce_field()` with CSRF verification in handler
- **Sanitize callbacks:** All Customizer settings have explicit `sanitize_callback` (text, kses, checkbox)
- **Form handling:** Newsletter signup routes through `admin_post` action with nonce + email validation
- **Permissions:** Check `is_active_sidebar()` before rendering widgets
- **Performance:** Script/style versions use `PEPTIDE_STARTER_VERSION` constant for cache busting
- **Accessibility:** Skip-to-main-content link (header.php line 20), WCAG AA color contrast, focus indicators

## Testing & CI/CD

- **CI pipeline** (`.github/workflows/deploy.yml`):
  1. PHP lint (`php -l`) on all PHP files
  2. PHPCS with WordPress-Core standard
  3. JS syntax check (`node --check`) on all JS files
  4. Deploy via rsync (only if validation passes)
  5. Post-deploy health check (HTTP 200)
- **Unit tests** (`tests/`): PHPUnit with WordPress test framework
  - `test-functions.php` — helpers, sanitizers, filters
  - `test-theme-setup.php` — theme supports, menus, widgets, scripts
- **rsync safety**: Dev-only files excluded from deploy (tests/, vendor/, composer.json, phpunit.xml, .phpcs.xml.dist)

## Browser Support

- Chrome/Edge (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- iOS Safari 12+
- Chrome Android

## Version

**Current:** v1.3.1 (defined in `style.css` header line 7 and `functions.php` line 14 constant)

When releasing a new version, update both locations.

## Related Documentation

- **CONVENTIONS.md** — How to add templates, components, plugin integrations
- **CLAUDE.md** — Developer context and quick reference
- **README.md** — User-facing feature list and installation
- **CHANGELOG.md** — Release history
