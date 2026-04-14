# Peptide Starter Theme

A premium, accessible WordPress theme for the Peptide Repo scientific peptide reference database.

## Features

- **Modern Design** - Clean, minimalist aesthetic inspired by Apple's human interface guidelines
- **Dark Mode Support** - Full dark mode via `prefers-color-scheme` and manual toggle
- **Accessibility** - WCAG AA compliant with proper focus indicators and semantic HTML
- **Plugin Harmony** - Respects plugin CSS namespaces (`.pn-*` for Peptide News plugin)
- **Responsive Design** - Mobile-first approach with proper breakpoints (480px, 768px, 1024px, 1440px)
- **Typography** - Inter font with 1.125x modular scale for excellent readability
- **Component Library** - Pre-built buttons, cards, forms, badges, alerts, and pagination
- **Customizer Support** - Theme options for hero title, subtitle, search placeholder, footer text
- **Performance** - Lightweight CSS architecture using custom properties instead of !important

## Installation

1. Download `peptide-starter.zip`
2. Go to WordPress Admin → Appearance → Themes
3. Click "Add New" → "Upload Theme"
4. Select the zip file and click "Install Now"
5. Click "Activate" to use the theme

## Theme Structure

```
peptide-starter/
├── style.css              # Main stylesheet with all CSS
├── functions.php          # Theme setup and functionality
├── header.php             # Global header with navigation
├── footer.php             # Global footer with widgets
├── index.php              # Fallback template
├── front-page.php         # Home page with hero + news feed
├── page.php               # Generic page template
├── archive-peptide.php    # Peptide archive/database view
├── single-peptide.php     # Single peptide detail page
├── 404.php                # 404 error page
├── searchform.php         # Search form template
├── screenshot.png         # Theme screenshot for WordPress
├── assets/
│   └── js/
│       ├── navigation.js  # Mobile menu and nav functionality
│       └── theme.js       # Dark mode toggle and utilities
├── template-parts/
│   ├── content.php        # Post/page content template
│   └── content-none.php   # Empty state template
└── languages/             # Translation files
```

## Design System

### Color Palette

**Light Mode:**
- Primary: `#0066CC` (Blue)
- Text Primary: `#111827` (Near Black)
- Text Secondary: `#6B7280` (Gray)
- Background: `#FFFFFF` (White)

**Dark Mode:**
- Primary: `#3B82F6` (Bright Blue)
- Text Primary: `#F9FAFB` (Near White)
- Text Secondary: `#D1D5DB` (Light Gray)
- Background: `#111827` (Near Black)

### Typography

All sizes use a **1.125x modular scale** with **8px grid base**:

- **H1**: 48px, 700 weight
- **H2**: 40px, 700 weight
- **H3**: 32px, 600 weight
- **H4**: 24px, 600 weight
- **H5**: 20px, 700 weight (fixed from 18px per Design Director)
- **H6**: 16px, 500 weight
- **Body**: 14px, 400 weight (fixed from 16px per Design Director)

Font family: Inter (with system font fallback)

### Spacing

8px base unit grid:
- `--spacing-xs`: 4px
- `--spacing-sm`: 8px
- `--spacing-md`: 16px
- `--spacing-lg`: 24px
- `--spacing-xl`: 32px
- `--spacing-2xl`: 48px
- `--spacing-3xl`: 64px
- `--spacing-4xl`: 80px
- `--spacing-5xl`: 96px

### Components

**Buttons:**
- `.ps-btn` - Base button
- `.ps-btn-primary` - Primary action (blue)
- `.ps-btn-secondary` - Secondary action (outlined)
- `.ps-btn-tertiary` - Tertiary action (transparent)
- `.ps-btn-sm`, `.ps-btn-lg`, `.ps-btn-full` - Size variants

**Cards:**
- `.ps-card` - Standard card with border and hover effect

**Forms:**
- Proper focus states with 15% opacity focus ring
- Error states with red border and light red background
- Support for text, email, password, search, number, url inputs

**Alerts:**
- `.ps-alert-info` - Blue alert
- `.ps-alert-success` - Green alert
- `.ps-alert-warning` - Amber alert
- `.ps-alert-error` - Red alert

**Badges & Tags:**
- `.ps-badge` - Static badge
- `.ps-tag` - Dismissible tag

## Dark Mode

### How it Works

The theme detects dark mode in this order:
1. User's explicit choice via toggle (stored in `localStorage`)
2. System preference via `prefers-color-scheme: dark`
3. Default to light mode

The theme applies dark colors via:
- `@media (prefers-color-scheme: dark)` - System preference
- `html[data-theme="dark"]` - User override (takes precedence)

### Plugin Integration

The theme broadcasts dark mode changes to plugins via:

1. **Custom Event**: `themechange` event on `document`
   ```javascript
   document.addEventListener('themechange', (e) => {
     console.log('Theme is now:', e.detail.theme);
   });
   ```

2. **Data Attribute**: `data-theme` on `<html>`
   ```javascript
   const theme = document.documentElement.getAttribute('data-theme');
   ```

3. **CSS Custom Properties**: All colors controlled via CSS variables

## Plugin Compatibility

### Peptide News Plugin
The theme provides optimal styling for the Peptide News plugin:
- News feed grid adapts to screen size (1-4 columns)
- Plugin `.pn-*` classes are untouched by theme
- Plugin handles its own card styling
- Dark mode colors available via CSS custom properties

### WooCommerce
- Cart icon in header (if WooCommerce is active)
- Lightweight integration without full theme styling

## Customizer Options

Go to **Appearance → Customize** to modify:
- **Hero Title** - Main headline on home page
- **Hero Subtitle** - Subheading under title
- **Search Placeholder** - Text in search input
- **Footer Copyright** - Copyright/attribution text
- **Dark Mode Default** - Enable dark mode on first visit

## Accessibility

The theme meets **WCAG AA standards**:
- All color combinations have 4.5:1+ contrast ratio
- Proper heading hierarchy (H1-H6)
- Focus indicators on all interactive elements (2px outline)
- Keyboard navigation support
- Skip-to-main-content link
- Semantic HTML with proper landmarks
- Form labels properly associated with inputs
- Alt text for images

## Responsive Breakpoints

- **Mobile**: 320px - 479px
- **Tablet**: 480px - 767px
- **Desktop**: 768px - 1023px
- **Large**: 1024px - 1439px
- **Extra Large**: 1440px+

The theme uses **mobile-first CSS** - styles start mobile and add complexity at larger breakpoints.

## Performance

- **No `!important` on global elements** - Prevents plugin conflicts
- **CSS Custom Properties** - Single source of truth for design tokens
- **Minimal JavaScript** - Only essential interactivity
- **Font loading** - Inter from Google Fonts with `display=swap`
- **Proper scoping** - Theme classes prefixed with `.ps-` to avoid conflicts

## Development

### Adding Custom Styles

Modify `style.css` sections:
1. Components - Add new component classes
2. Pages - Add page-specific styles
3. Responsive - Add mobile/tablet/desktop rules

### CSS Architecture

The stylesheet is organized in this order:
1. Reset
2. CSS Custom Properties (variables)
3. Typography
4. Global Styles
5. Components
6. Regions (header, footer)
7. Pages
8. Animations
9. Accessibility
10. Dark Mode
11. Responsive

### Adding New Templates

Create new template files following WordPress standards:
- `page-{slug}.php` - Specific page templates
- `archive-{post-type}.php` - Archive templates
- `single-{post-type}.php` - Single post templates
- `template-parts/{name}.php` - Reusable template parts

## Browser Support

- Chrome/Edge (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- iOS Safari 12+
- Chrome Android

## License

GPL v2 or later - See LICENSE file

## Credits

Designed with principles from:
- Apple Human Interface Guidelines (2025)
- Modern Scientific Databases (PubMed, Nature)
- Premium SaaS Design (Linear, Stripe, Vercel)
- WordPress Best Practices

## Support

For issues and feature requests, visit: https://github.com/peptiderepo/peptide-starter

## Changelog

### Version 1.0.0 (Initial Release)
- Initial theme launch
- All core features implemented
- Full dark mode support
- Plugin harmony architecture
- Accessibility compliance (WCAG AA)
