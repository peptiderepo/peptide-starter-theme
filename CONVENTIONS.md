# Peptide Starter Theme - Conventions & Development Guide

## Naming Conventions

### PHP Functions

All custom PHP functions are prefixed with `peptide_starter_`:

```php
// Good
function peptide_starter_get_hero_title() { }
function peptide_starter_setup() { }
function peptide_starter_widgets_init() { }
function peptide_starter_pagination() { }

// Bad
function get_hero_title() { }
function setup_theme() { }
```

**Pattern:** `peptide_starter_{verb}_{noun}` or `peptide_starter_get_{option}`

**Common function types:**
- `peptide_starter_get_*()` — Retrieve theme mod or option
- `peptide_starter_{noun}_init()` — Initialization functions
- `peptide_starter_is_*()` — Boolean checks
- `peptide_starter_render_*()` — Output HTML

### CSS Classes

All theme CSS classes are prefixed with `.ps-`:

```css
/* Components */
.ps-btn               /* Button component */
.ps-btn-primary       /* Button variant */
.ps-btn-sm            /* Button size */
.ps-card              /* Card component */
.ps-badge             /* Badge component */
.ps-alert-info        /* Alert variant */

/* Utilities */
.ps-container         /* Max-width wrapper */
.ps-pagination        /* Pagination nav */
.ps-search-form       /* Search form */
.ps-search-input      /* Search input */

/* Regions */
.site-header          /* No prefix; global region */
.site-footer          /* No prefix; global region */
.site-main            /* No prefix; global region */
.hero-section         /* Hero region specific */
```

**Pattern:** `.ps-{component}` for components, `.ps-{component}-{variant}` for variants

**Reserved prefixes (do not use):**
- `.pn-*` — Peptide News plugin
- `.psa-*` — Peptide Search AI plugin
- `.prab-*` — PRAutoBlogger plugin

### CSS Variables (Custom Properties)

All design tokens are defined in `style.css :root`:

```css
/* Colors */
--text-primary        /* Main text color */
--text-secondary      /* Secondary text color */
--bg-primary          /* Main background */
--bg-secondary        /* Secondary background */

/* Spacing (8px grid base) */
--spacing-xs: 4px     /* 0.5 × base */
--spacing-sm: 8px     /* 1 × base */
--spacing-md: 16px    /* 2 × base */
--spacing-lg: 24px    /* 3 × base */
--spacing-xl: 32px    /* 4 × base */

/* Typography */
--font-sans           /* Primary font family */
--font-mono           /* Monospace font */
--text-h1             /* H1 size */
--text-h1-weight      /* H1 weight */
```

**Usage:**
```css
.ps-card {
  padding: var(--spacing-md);
  color: var(--text-primary);
}
```

### Template Part Naming

Template parts in `template-parts/` follow WordPress convention:

```
template-parts/content.php              /* Default loop item */
template-parts/content-{post-type}.php  /* CPT-specific */
template-parts/content-none.php         /* Empty state */
template-parts/{section}-{name}.php     /* Specific parts (if added) */
```

**Calling template parts:**
```php
get_template_part( 'template-parts/content', get_post_type() );
get_template_part( 'template-parts/content', 'none' );
```

### Hook Names

Custom hooks use the `peptide_starter_` prefix:

```php
// Filters
apply_filters( 'peptide_starter_show_newsletter', true );

// Actions (if any added in future)
do_action( 'peptide_starter_hero_before' );
```

### File Organization

- **Root:** Theme core files (functions.php, header.php, footer.php, etc.)
- **assets/js/:** Client-side scripts only; no bundling
- **assets/css/:** (None; all CSS in style.css)
- **languages/:** Translation files
- **template-parts/:** Reusable template parts

## Step-by-Step Guides

### How to Add a New Template

**Scenario:** Create a custom landing page template

1. Create file: `landing-{name}.php`
   ```php
   <?php
   /**
    * Landing Page: {Description}
    * Template Name: {Display Name}
    *
    * @package peptide-starter
    */

   get_header();
   ?>

   <main id="main" class="site-main">
     <!-- Your content here -->
   </main>

   <?php get_footer();
   ```

2. Add template header comment:
   ```php
   /**
    * Template Name: My Custom Landing Page
    * Template Post Type: page
    */
   ```

3. The template will appear in WordPress Page > Template dropdown

4. Test the template:
   - Create/edit a page and select the template
   - Verify hero, dark mode toggle, footer all appear correctly

**Best practices:**
- Always include `get_header()` and `get_footer()`
- Wrap main content in `<main id="main" class="site-main">`
- Use `.ps-container` for consistent max-width
- Use existing component classes (`.ps-card`, `.ps-btn`, etc.)
- Keep templates under 150 lines; extract complex parts to `template-parts/`

### How to Add a New Plugin Integration

**Scenario:** Integrate a new plugin (e.g., "Research Database") via shortcode

1. **In front-page.php**, add a conditional shortcode block:
   ```php
   <?php if ( shortcode_exists( 'research_db' ) ) { ?>
     <section class="research-db-section">
       <div class="ps-container">
         <h2><?php esc_html_e( 'Research Database', 'peptide-starter' ); ?></h2>
         <?php echo do_shortcode( '[research_db limit="10"]' ); ?>
       </div>
     </section>
   <?php } ?>
   ```

2. **In functions.php**, optionally add inline overrides if plugin CSS needs adjustment:
   ```php
   function peptide_starter_plugin_overrides() {
     wp_add_inline_style( 'peptide-starter-style', '
       /* Override plugin styles here */
       .rdb-card { /* example */ }
     ' );
   }
   add_action( 'wp_enqueue_scripts', 'peptide_starter_plugin_overrides', 99 );
   ```

3. **Document the integration** in CLAUDE.md:
   - Plugin namespace (e.g., `.rdb-*`)
   - Shortcode name and parameters
   - Expected styling classes

4. **If plugin needs dark mode coordination:**
   - Ask plugin author to listen to `themechange` event (see ARCHITECTURE.md)
   - Or have plugin read `html[data-theme]` attribute
   - Provide plugin with CSS custom properties: `getComputedStyle(document.documentElement).getPropertyValue('--text-primary')`

5. **Test:**
   - Activate plugin and visit front page
   - Verify shortcode renders
   - Toggle dark mode; verify plugin responds
   - Deactivate plugin; verify page still renders (fallback gracefully)

**Important:** Always wrap shortcodes in `shortcode_exists()` checks so theme doesn't break if plugin is missing.

### How to Add a New CSS Component

**Scenario:** Create a new "pill" button style (small, rounded, inline)

1. **In style.css**, find the Components section (around line 500)

2. **Add new component class:**
   ```css
   /* Pill buttons */
   .ps-btn-pill {
     display: inline-block;
     padding: var(--spacing-xs) var(--spacing-sm);
     border-radius: 9999px;
     font-size: 12px;
     font-weight: 600;
     text-transform: uppercase;
     letter-spacing: 0.5px;
   }

   .ps-btn-pill.ps-btn-primary {
     background-color: var(--color-primary);
     color: white;
   }

   .ps-btn-pill.ps-btn-secondary {
     background-color: transparent;
     border: 1px solid var(--color-primary);
     color: var(--color-primary);
   }
   ```

3. **Add dark mode support** (in Dark Mode section, around line 1200):
   ```css
   html[data-theme="dark"] .ps-btn-pill.ps-btn-secondary {
     border-color: var(--color-primary-dark);
     color: var(--color-primary-dark);
   }
   ```

4. **Add responsive sizes if needed** (in Responsive section, around line 1400):
   ```css
   @media (max-width: 767px) {
     .ps-btn-pill {
       padding: var(--spacing-xs) var(--spacing-xs);
     }
   }
   ```

5. **Usage in templates:**
   ```html
   <span class="ps-btn ps-btn-pill ps-btn-primary">Beta</span>
   ```

6. **Test:**
   - Light mode: verify colors, sizing, alignment
   - Dark mode: toggle and verify contrast
   - Mobile (480px): verify responsive adjustments
   - Keyboard: focus state should be visible

**Best practices:**
- Use CSS variables (`var(--spacing-*)`, `var(--color-*)`) not hardcoded values
- Avoid `!important` unless absolutely necessary
- Include dark mode support immediately (no `.ps-*` is light-only)
- Group related rules with comments
- Keep component definitions self-contained (<100 lines)

### How to Add a New Customizer Setting

**Scenario:** Add a new "Primary Accent Color" customizer option

1. **In functions.php** (around line 143), add a new section:
   ```php
   $wp_customize->add_section( 'peptide_starter_colors', array(
     'title'    => esc_html__( 'Colors', 'peptide-starter' ),
     'priority' => 45,
   ) );
   ```

2. **Add a setting:**
   ```php
   $wp_customize->add_setting( 'primary_accent_color', array(
     'default'           => '#0066CC',
     'transport'         => 'postMessage',
     'sanitize_callback' => 'sanitize_hex_color',
   ) );
   ```

3. **Add a control:**
   ```php
   $wp_customize->add_control( new WP_Customize_Color_Control(
     $wp_customize,
     'primary_accent_color',
     array(
       'label'    => esc_html__( 'Primary Accent Color', 'peptide-starter' ),
       'section'  => 'peptide_starter_colors',
       'settings' => 'primary_accent_color',
     )
   ) );
   ```

4. **Create a getter function:**
   ```php
   function peptide_starter_get_primary_accent_color() {
     return get_theme_mod( 'primary_accent_color', '#0066CC' );
   }
   ```

5. **Output in front-end** (e.g., in footer.php):
   ```php
   <style>
     :root {
       --color-accent: <?php echo esc_attr( peptide_starter_get_primary_accent_color() ); ?>;
     }
   </style>
   ```

   Or better, use inline style in `wp_head`:
   ```php
   function peptide_starter_custom_colors() {
     $accent = peptide_starter_get_primary_accent_color();
     ?>
     <style>
       :root {
         --color-accent: <?php echo esc_attr( $accent ); ?>;
       }
     </style>
     <?php
   }
   add_action( 'wp_head', 'peptide_starter_custom_colors' );
   ```

6. **Reference in CSS:**
   ```css
   .ps-btn-accent {
     background-color: var(--color-accent);
   }
   ```

7. **Test:**
   - Appearance → Customize → Colors → Primary Accent Color
   - Change color and verify live preview
   - Save and reload page

**Transport options:**
- `'transport' => 'postMessage'` — Instant preview (needs JS listener)
- `'transport' => 'refresh'` — Full page refresh

For color/text changes, use `postMessage` with a JS listener in theme.js:
```javascript
wp_customize.bind( 'setting:primary_accent_color', function( setting ) {
  setting.bind( function( newval ) {
    document.documentElement.style.setProperty('--color-accent', newval);
  });
});
```

## Error Handling Patterns

### Theme Doesn't Break If Plugin Missing

**Pattern: Graceful Degradation**
```php
// Good — shortcode_exists check
if ( shortcode_exists( 'peptide_search' ) ) {
  echo do_shortcode( '[peptide_search]' );
} else {
  get_search_form();  // Fallback to WordPress default
}

// Bad — assumes plugin exists
echo do_shortcode( '[peptide_search]' );  // Renders [peptide_search] as text if missing
```

### Menu Fallback

**In header.php:**
```php
wp_nav_menu( array(
  'theme_location' => 'primary',
  'fallback_cb'    => 'peptide_starter_primary_menu_fallback',  // Function defined in header
  'walker'         => new Peptide_Starter_Nav_Walker(),
) );

function peptide_starter_primary_menu_fallback() {
  // Renders Home, About, Peptides links if no menu assigned
}
```

### Widget Area Check

**In footer.php:**
```php
if ( is_active_sidebar( 'footer-1' ) ) {
  dynamic_sidebar( 'footer-1' );
} else {
  // Render default content
  echo '<h3>' . esc_html__( 'About', 'peptide-starter' ) . '</h3>';
}
```

### Class Existence Check

**For WooCommerce:**
```php
if ( class_exists( 'WooCommerce' ) ) {
  // Render cart icon
}
```

**For post types:**
```php
if ( post_type_exists( 'peptide' ) ) {
  echo '<a href="' . get_post_type_archive_link( 'peptide' ) . '">' . __( 'Peptides' ) . '</a>';
}
```

### Input Validation

All user-facing output must be escaped:

```php
// Good
echo esc_html( get_the_title() );
echo esc_url( home_url( '/' ) );
echo esc_attr( $html_attribute_value );

// Bad
echo get_the_title();
echo home_url( '/' );
```

## Dark Mode Implementation Guide

### How Dark Mode Works in Peptide Starter

1. **Inline script in `wp_head`** (functions.php, line 406-422)
   - Runs immediately before CSS loads
   - Prevents white flash by setting `html[data-theme]` early
   - Checks in order: localStorage → system pref → customizer default

2. **CSS selectors** (style.css, line ~1200 "Dark Mode")
   - Light mode: default colors in `:root`
   - Dark mode: `html[data-theme="dark"]` selector overrides

3. **JavaScript listener** (theme.js, line 47-63)
   - Responds to dark mode toggle button
   - Updates localStorage
   - Dispatches `themechange` event to plugins
   - Updates sun/moon icon visibility

### Adding Dark Mode to a New Component

**Example: New Alert Style**

1. **Define light mode in CSS:**
   ```css
   .ps-alert-custom {
     padding: var(--spacing-md);
     background-color: #E3F2FD;  /* Light blue */
     color: #0D47A1;              /* Dark blue text */
     border: 1px solid #90CAF9;
   }
   ```

2. **Add dark mode override:**
   ```css
   html[data-theme="dark"] .ps-alert-custom {
     background-color: #1A237E;  /* Dark blue bg */
     color: #E3F2FD;              /* Light text */
     border-color: #3F51B5;       /* Darker border */
   }
   ```

3. **Test:**
   - Load page in light mode → verify colors
   - Toggle dark mode → verify override colors are applied
   - Open DevTools → verify `html[data-theme="dark"]` attribute set
   - Check localStorage `peptide-starter-theme` is updated

### Helping Plugins Support Dark Mode

**If a plugin doesn't listen to dark mode changes:**

1. Ask plugin author to listen to the `themechange` event:
   ```javascript
   document.addEventListener('themechange', (e) => {
     const theme = e.detail.theme;
     // Update plugin styles based on theme
   });
   ```

2. Or use a MutationObserver to watch for `data-theme` changes:
   ```javascript
   const observer = new MutationObserver(() => {
     const theme = document.documentElement.getAttribute('data-theme');
     // Update styles
   });
   observer.observe(document.documentElement, { attributes: true });
   ```

3. Or read CSS variables in the plugin stylesheet:
   ```css
   .plugin-card {
     color: var(--text-primary);
     background-color: var(--bg-secondary);
   }
   ```

### CSS Variables for Dark Mode

All available variables (defined in `:root`):

```css
--text-primary          /* Main text (light: #111827, dark: #F9FAFB) */
--text-secondary        /* Secondary text (light: #6B7280, dark: #D1D5DB) */
--bg-primary            /* Main background (light: #FFFFFF, dark: #111827) */
--bg-secondary          /* Secondary bg (light: #F3F4F6, dark: #1F2937) */
--color-primary         /* Primary accent (light: #0066CC, dark: #3B82F6) */
--border-color          /* Border color (light: #E5E7EB, dark: #374151) */
```

## Testing Checklist

When modifying the theme, verify:

- [ ] **Light mode** — All text readable, colors correct
- [ ] **Dark mode** — Toggle to dark, verify colors swap, no white flash on reload
- [ ] **Mobile** (480px) — Navigation collapses, search/dark mode icons present
- [ ] **Tablet** (768px) — Layout adapts, footer 2-column if needed
- [ ] **Desktop** (1024px+) — Full layout renders correctly
- [ ] **Plugins active** — Shortcodes render, no JS errors in console
- [ ] **Plugins inactive** — Graceful fallbacks work (search form, no broken layout)
- [ ] **Accessibility** — Tab through page, all interactive elements reachable
- [ ] **Focus states** — Every button/link has visible focus outline
- [ ] **Keyboard navigation** — Escape closes overlays, arrow keys in menu if relevant

## Common Tasks & Commands

**Check JavaScript syntax:**
```bash
node --check assets/js/theme.js
node --check assets/js/navigation.js
```

**View theme version:**
```php
echo PEPTIDE_STARTER_VERSION;  // Currently: 1.3.1
```

**Update theme version (when releasing):**
1. Edit `style.css` line 7: `Version: X.Y.Z`
2. Edit `functions.php` line 14: `define( 'PEPTIDE_STARTER_VERSION', 'X.Y.Z' )`

**Deploy to production:**
- Push to `main` branch on GitHub
- GitHub Actions runs rsync to Hostinger via SSH
- LiteSpeed cache clears automatically

**Debug active plugins:**
```php
// In functions.php or template
$active = get_option( 'active_plugins' );
error_log( print_r( $active, true ) );
```

**View Customizer settings:**
```php
echo get_theme_mod( 'hero_title' );
echo get_theme_mod( 'dark_mode_default', false );
```

## Trust model for request headers (v1.5.2)

Never trust `HTTP_X_FORWARDED_FOR`, `HTTP_CF_CONNECTING_IP`, or any other
client-controlled header without first validating the peer. Always read
the client IP via `peptide_starter_get_client_ip()` — it handles the
Cloudflare-peer check, the XFF opt-in, and the `REMOTE_ADDR` fallback.

If a new feature needs to trust a different proxy header (e.g. a new CDN
in front of CF):

1. Add the upstream proxy's IP ranges to a new file under `inc/`.
2. Extend `peptide_starter_get_client_ip()` with a new validated branch
   guarded by a filter defaulted to off.
3. Never accept a header whose source can't be validated at the TCP
   layer.

Refresh the Cloudflare range snapshot in `inc/cloudflare-ips.php`
quarterly. Out-of-band bumps go through the
`peptide_starter_cloudflare_ip_ranges` filter.

## Security Patterns (v1.5.1)

### Rate-limit a handler

Every new public POST handler must be rate-limited.

```php
// After nonce + honeypot checks.
$identifier = peptide_starter_hash_identifier( strtolower( $email ) ); // or 'ip' for IP-only bucket
if ( ! Peptide_Starter_Rate_Limiter::check( 'login', $identifier ) ) {
    // Respond with the SAME message the real-validation-failure path uses.
    wp_send_json_error( array( 'message' => peptide_starter_login_failure_message() ) );
}
Peptide_Starter_Rate_Limiter::record( 'login', $identifier ); // on failure
// ...successful operation...
Peptide_Starter_Rate_Limiter::reset( 'login', $identifier ); // on success
```

Pick a short action slug. Register its limit in `inc/config.php` under
`rate_limits`. Never hardcode numbers in the handler.

### Honeypot a form

1. Render the trap in the form template part:

```php
<?php peptide_starter_render_honeypot( 'myform' ); ?>
```

2. In the handler, right after nonce verification:

```php
if ( peptide_starter_honeypot_triggered( 'ps_hp_myform' ) ) {
    // Fake-success response — do NOT return an error. Bots learn from errors.
    wp_send_json_success( array( 'message' => __( 'OK', 'peptide-starter' ) ) );
}
```

### Gate a template

At the very top of the template, after the `ABSPATH` guard, before
`get_header()`:

```php
peptide_starter_require_login();
```

This redirects unauthenticated users to `/auth?redirect_to=current-uri`
and unverified users to `/profile?verify_required=1`. Do not reinvent
this per-template.

### CSV-safe export

Route every cell through the helper before `fputcsv`:

```php
fputcsv( $output, array(
    peptide_starter_csv_safe( $email ),
    peptide_starter_csv_safe( $date ),
) );
```

### Error unification

Login + registration failures must emit the same message regardless of
which validation check failed. Use:

- `peptide_starter_login_failure_message()` — "Invalid email or password."
- `peptide_starter_register_failure_message()` — "Unable to create account..."

Branching on failure reason is an enumeration oracle. Don't do it.

### Adding a new security threshold

Add a key to the defaults array in `inc/config.php` and always read it
via `peptide_starter_config_get()` or the shortcut helpers. No magic
numbers in handler files.

## Related Documentation

- **ARCHITECTURE.md** — System design, data flows, integrations
- **CLAUDE.md** — Developer context and quick reference
- **README.md** — User-facing feature documentation
- **CHANGELOG.md** — Version history
