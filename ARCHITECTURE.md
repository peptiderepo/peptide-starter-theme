# Peptide Starter Theme - Architecture

## Overview

Peptide Starter (peptide-starter) is a premium, accessible WordPress theme for peptiderepo.com — a scientific peptide reference database. The theme is a classic (non-block) WordPress theme that assembles a modern, responsive frontend by combining a monolithic CSS architecture with vanilla JavaScript. It integrates seamlessly with five companion plugins (Peptide Search AI, Peptide News, PRAutoBlogger, Peptide Tools, Peptide Tracker, Peptide Community) via shortcodes, coordinates dark mode across the ecosystem via CSS custom properties and the DOM `data-theme` attribute, and provides customizable hero, navigation, authentication, and footer sections.

## Directory Structure

```
peptide-starter/
├── style.css                    # All theme CSS. Architecture: reset → tokens → typography →
│                                 # globals → components → regions → pages → animations →
│                                 # a11y → dark mode → responsive → new feature sections
│
├── functions.php                # Theme setup entry point (~206 lines)
│                                 # - Constants, feature module loading
│                                 # - Theme support registration
│                                 # - Script/style enqueueing (incl conditional)
│                                 # - Widget areas, newsletter handler, dark mode inline script
│
├── inc/
│   ├── helpers.php              # Nav walker, custom logo, hero/footer getters, pagination
│   ├── customizer.php           # Customizer sections, settings, sanitize callbacks
│   ├── auth-handlers.php        # AJAX login + registration handlers
│   ├── contact-handler.php      # AJAX contact form handler (settings panel)
│   ├── page-setup.php           # Auto-create pages on theme activation
│   └── newsletter-admin.php     # Admin page to view/export subscriber emails
│
├── header.php                   # Global header (~190 lines)
│                                 # - Site logo, primary navigation with dropdowns
│                                 # - Sign In / User menu (state-aware)
│                                 # - Settings icon, language placeholder
│                                 # - Search, dark mode toggle, cart, mobile menu
│                                 # - Search overlay, fallback nav
│
├── footer.php                   # Global footer (~100 lines)
│                                 # - Newsletter signup (front page via template part)
│                                 # - 4-column widget grid with updated defaults
│                                 # - Copyright, disclaimer, language links
│                                 # - Settings panel include
│
├── front-page.php               # Home page template (~100 lines)
│                                 # - Hero section (title, subtitle, search, CTAs)
│                                 # - Research Modules grid (6 cards)
│                                 # - [prautoblogger_posts] shortcode
│                                 # - [peptide_news] shortcode
│
├── page-calculator.php          # Calculator page template → [peptide_tools_calculator]
├── page-protocol-builder.php    # Protocol Builder template → [peptide_tools_protocol_builder]
├── page-tracker.php             # Tracker template → [peptide_tracker]
├── page-subject-log.php         # Subject Log template → [peptide_tracker_subject_log]
├── page-documentation.php       # Documentation/SOP page — two-column layout with ToC sidebar
├── page-directory.php           # Peptide Directory template → [peptide_directory]
├── page-science-feed.php        # Science Feed template → [peptide_news] + newsletter
├── page-profile.php             # User Profile template → [peptide_community_profile]
├── page-auth.php                # Sign In / Register — branded auth forms with AJAX
│
├── page.php                     # Generic page template
├── index.php                    # Fallback template
├── single-peptide.php           # Peptide detail page (CPT)
├── archive-peptide.php          # Peptide archive (CPT grid)
├── 404.php                      # 404 error page
├── searchform.php               # Search form template
│
├── template-parts/
│   ├── content.php              # Default post/page loop item
│   ├── content-peptide.php      # Peptide CPT card template
│   ├── content-none.php         # Empty state template
│   ├── module-cards.php         # Research Modules 6-card grid (front page)
│   ├── newsletter-signup.php    # Reusable newsletter signup section
│   └── settings-panel.php       # Slide-out support/contact panel
│
├── assets/js/
│   ├── navigation.js            # Mobile menu toggle (85 lines)
│   ├── theme.js                 # Dark mode + search overlay + utilities (210 lines)
│   ├── documentation.js         # ToC generation + scroll spy for docs page (145 lines)
│   ├── auth.js                  # Auth form toggle, validation, AJAX (192 lines)
│   └── settings-panel.js        # Settings panel open/close + contact form (163 lines)
│
├── languages/                   # Translation files (.pot, .po, .mo)
├── tests/                       # PHPUnit tests
├── .phpcs.xml.dist              # PHPCS config
├── phpunit.xml                  # PHPUnit config
├── composer.json                # Dev dependencies
├── README.md                    # User-facing docs
├── CLAUDE.md                    # Developer context
├── CHANGELOG.md                 # Version history
└── screenshot.png               # Theme preview image
```

## Navigation Structure

```
Home | Tools ▾ | My Data ▾ | Resources ▾ | [Sign In / Username ▾] ⚙ 🌐 🔍 🌙

Tools dropdown:
  ├── Calculator → /calculator
  ├── Protocol Builder → /protocol-builder
  └── Tracker → /tracker

My Data dropdown:
  ├── Peptides → /peptides
  └── Subject Log → /subject-log

Resources dropdown:
  ├── Documentation → /documentation
  └── Science Feed → /news

User menu (logged in):
  ├── My Profile → /profile
  ├── Tracker → /tracker
  ├── Subject Log → /subject-log
  └── Sign Out
```

## Page Template Map

| Page Title | Slug | Template File | Plugin Shortcode |
|------------|------|---------------|-----------------|
| Calculator | calculator | page-calculator.php | [peptide_tools_calculator] |
| Protocol Builder | protocol-builder | page-protocol-builder.php | [peptide_tools_protocol_builder] |
| Tracker | tracker | page-tracker.php | [peptide_tracker] |
| Subject Log | subject-log | page-subject-log.php | [peptide_tracker_subject_log] |
| Documentation | documentation | page-documentation.php | (WordPress content + JS ToC) |
| Peptide Directory | peptides | page-directory.php | [peptide_directory] |
| Science Feed | news | page-science-feed.php | [peptide_news] |
| Profile | profile | page-profile.php | [peptide_community_profile] |
| Sign In | auth | page-auth.php | (Custom auth forms) |

Pages are auto-created on theme activation via `inc/page-setup.php`.

## Data Flow & Architecture Diagrams

### Front Page Assembly

```
front-page.php
    ├─→ Hero Section (title, subtitle, search, CTAs)
    │   └─→ [peptide_search] shortcode (Peptide Search AI plugin)
    │
    ├─→ Research Modules Grid (template-parts/module-cards.php)
    │   └─→ 6 cards linking to tool/section pages
    │       └─→ Checks shortcode_exists() for graceful degradation
    │
    ├─→ [prautoblogger_posts] shortcode (PRAutoBlogger plugin)
    │
    └─→ [peptide_news] shortcode (Peptide News plugin)
```

### Authentication Flow

```
page-auth.php
    ├─→ Sign In form → auth.js AJAX → inc/auth-handlers.php
    │   └─→ wp_signon() → redirect to original page
    │
    └─→ Register form → auth.js AJAX → inc/auth-handlers.php
        └─→ wp_create_user() → auto-login → redirect to /profile
```

### Dark Mode Coordination

```
functions.php (wp_head inline script)
    └─→ Sets html[data-theme] from localStorage → system pref → customizer default
        ├─→ style.css reads data-theme for color overrides
        └─→ theme.js dispatches 'themechange' event to plugins
```

## External Integrations

### Plugin Integrations (via Shortcodes)

1. **Peptide Search AI** (`[peptide_search]`) — Hero + search overlay. Namespace: `.psa-*`
2. **Peptide News** (`[peptide_news]`) — Front page + science feed. Namespace: `.pn-*`
3. **PRAutoBlogger** (`[prautoblogger_posts]`) — Front page articles. Namespace: `.prab-*`
4. **Peptide Tools** (`[peptide_tools_calculator]`, `[peptide_tools_protocol_builder]`) — Tool pages
5. **Peptide Tracker** (`[peptide_tracker]`, `[peptide_tracker_subject_log]`) — Tracker pages
6. **Peptide Community** (`[peptide_community_profile]`, `[peptide_directory]`) — Profile + directory

All plugins checked with `shortcode_exists()` for graceful degradation.

## Key Architectural Decisions

1. **Classic Theme (Not FSE)** — Stability, control, plugin compatibility
2. **Monolithic CSS** — No build step, CSS custom properties for abstraction
3. **Vanilla JavaScript** — No bundler, direct browser ES6+, `node --check` validation
4. **Shortcode Integration** — Decouples plugins from theme via `shortcode_exists()`
5. **CSS Custom Properties for Dark Mode** — Single source of truth for colors
6. **No `!important` Policy** — Explicit specificity over cascade tricks
7. **Inline Dark Mode Script** — Prevents flash of wrong theme on load
8. **Split functions.php into inc/** — Keeps all files under 300 lines

## Security

- CSRF nonces on all forms (auth, newsletter, contact)
- All input sanitized at boundary (`sanitize_text_field`, `sanitize_email`, `sanitize_textarea_field`)
- All output escaped (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`)
- Login redirect validated with `wp_validate_redirect()` to prevent open redirects
- Auth form uses AJAX to avoid exposing credentials in URL
- Contact form topics restricted to allowlist

## Version

**Current:** v1.5.0

When releasing a new version, update `style.css` header (line 7) and `PEPTIDE_STARTER_VERSION` in `functions.php`.
