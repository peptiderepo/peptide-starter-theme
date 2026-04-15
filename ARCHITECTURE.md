# Peptide Starter Theme — Architecture

> **Cross-app context:** decisions that affect multiple plugins (Cloudflare AI Gateway routing, OpenRouter account sharing, the interface pattern, image-generation stack, social distributor choice) are recorded in `Peptide Repo CTO/docs/engineering/decisions/`. The incident runbook for cross-app failure modes is at `Peptide Repo CTO/docs/engineering/INCIDENT-RUNBOOK.md`. Read both before making decisions that cross plugin boundaries.

## Overview

Peptide Starter (slug: `peptide-starter`) is a premium classic WordPress
theme for peptiderepo.com — a scientific peptide reference database. It
assembles a modern, accessible frontend from a monolithic stylesheet, a
handful of vanilla-JS modules, and a collection of PHP modules under
`inc/`. The theme integrates with six companion plugins through
shortcodes (graceful degradation via `shortcode_exists`), coordinates
dark mode via CSS custom properties + a `data-theme` attribute, and — as
of v1.5.1 — runs a self-contained abuse-control layer (rate limiting,
honeypots, email verification) over its frontend auth surface.

## Directory Structure

```
peptide-starter/
├── style.css                      # All theme CSS. Section 26 = v1.5.1 additions.
├── functions.php                  # Theme setup entry point (<250 lines).
│                                   # - Constants, feature module loading (order matters)
│                                   # - Theme support + nav menus
│                                   # - Script/style enqueue (conditional per template)
│                                   # - Newsletter signup POST handler
│                                   # - Inline dark mode flash preventer in wp_head
│
├── inc/
│   ├── config.php                 # [v1.5.1] Security config — single source of truth.
│   │                               # Every threshold lives here; filterable.
│   ├── helpers.php                # Nav walker + menu fallback, logo, getters,
│   │                               # pagination, honeypot renderer, csv_safe,
│   │                               # require_login gate, safe referer.
│   ├── cloudflare-ips.php         # [v1.5.2] CF edge range snapshot +
│   │                               # CIDR matcher (v4 and v6).
│   ├── rate-limiter.php           # Peptide_Starter_Rate_Limiter +
│   │                               # peptide_starter_get_client_ip (peer-validated,
│   │                               # PSEC-007).
│   ├── email-verification.php     # [v1.5.1] Token send + verify route + resend
│   │                               # endpoint + user_is_verified().
│   ├── customizer.php             # Customizer sections/settings/controls.
│   ├── auth-handlers.php          # AJAX login + register — unified errors,
│   │                               # honeypot, rate limit, no auto-login.
│   ├── contact-handler.php        # AJAX contact — header-injection guard,
│   │                               # honeypot, rate limit, request IDs in mail.
│   ├── newsletter-admin.php       # Subscriber viewer + CSV-safe export +
│   │                               # /newsletter-unsubscribe route.
│   ├── mail-diagnostic.php        # [v1.5.1] Tools → Mail Test — wp_mail probe.
│   └── page-setup.php             # Auto-create pages on activation +
│                                    # v1.5.0 user migration (enrol into verify).
│
├── header.php                     # Site header, nav, icons, search overlay.
├── footer.php                     # Widget grid + copyright + settings panel.
├── front-page.php                 # Hero → module cards → plugin feeds.
│
├── page-calculator.php            # OPEN — reconstitution calculator (no PII).
├── page-protocol-builder.php      # GATED — require_login (builds saved protocols).
├── page-tracker.php               # GATED — require_login (per-user tracking).
├── page-subject-log.php           # GATED — require_login (user lab data).
├── page-directory.php             # Peptide directory.
├── page-documentation.php         # Two-column docs with ToC sidebar.
├── page-science-feed.php          # Science feed + newsletter signup.
├── page-profile.php               # Profile + verify-required banner + resend.
├── page-auth.php                  # Sign In / Register forms (honeypotted).
├── page.php, index.php, 404.php   # Standard fallbacks.
├── single-peptide.php             # Peptide CPT detail.
├── archive-peptide.php            # Peptide CPT archive.
│
├── template-parts/
│   ├── content.php, content-*.php # Loop / CPT / empty state.
│   ├── module-cards.php           # Front-page 6-card grid.
│   ├── newsletter-signup.php      # [v1.5.1] Signup + consent + honeypot.
│   └── settings-panel.php         # Slide-out support/contact panel + honeypot.
│
├── assets/js/
│   ├── navigation.js              # Mobile menu + body-overflow save/restore.
│   ├── theme.js                   # Dark mode + search overlay + plugin API.
│   ├── documentation.js           # ToC builder (text-slug IDs) + scroll spy.
│   ├── auth.js                    # Form toggle + AJAX + XSS-safe status.
│   └── settings-panel.js          # Open/close + focus trap + AJAX contact.
│
├── tests/
│   ├── bootstrap.php              # PHPUnit bootstrap.
│   ├── test-functions.php         # Legacy helper tests.
│   ├── test-theme-setup.php       # Theme setup tests.
│   ├── test-rate-limiter.php      # [v1.5.1] Rate limiter lifecycle + IP resolver.
│   ├── test-auth-handlers.php     # [v1.5.1] Login + register unified errors.
│   ├── test-email-verification.php# [v1.5.1] Token send/verify/reject.
│   ├── test-contact-handler.php   # [v1.5.1] Header injection + rate limit + honeypot.
│   ├── test-newsletter.php        # [v1.5.1] Signup + consent + CSV-safe.
│   └── test-auth-gate.php         # [v1.5.1] require_login redirects.
│
├── .phpcs.xml.dist                # PHPCS config (WordPress-Core).
├── phpunit.xml                    # PHPUnit config (theme suite).
├── composer.json                  # Dev-only dependencies.
├── CHANGELOG.md, CONVENTIONS.md, README.md, CLAUDE.md
└── screenshot.png
```

## Navigation Structure

```
Home | Tools ▾ | My Data ▾ | Resources ▾ | [Sign In / Username ▾] ⚙ 🌐 🔍 🌙

Tools:       Calculator (open) · Protocol Builder · Tracker
My Data:     Peptides · Subject Log
Resources:   Documentation · Science Feed
User menu:   My Profile · Tracker · Subject Log · Sign Out
```

## Page Template Map

| Page Title        | Slug             | Template                    | Gated     | Shortcode                           |
|-------------------|------------------|-----------------------------|-----------|-------------------------------------|
| Calculator        | calculator       | page-calculator.php         | open      | [peptide_tools_calculator]          |
| Protocol Builder  | protocol-builder | page-protocol-builder.php   | login+verify | [peptide_tools_protocol_builder] |
| Tracker           | tracker          | page-tracker.php            | login+verify | [peptide_tracker]                |
| Subject Log       | subject-log      | page-subject-log.php        | login+verify | [peptide_tracker_subject_log]    |
| Documentation     | documentation    | page-documentation.php      | open      | —                                   |
| Peptide Directory | peptides         | page-directory.php          | open      | [peptide_directory]                 |
| Science Feed      | news             | page-science-feed.php       | open      | [peptide_news]                      |
| Profile           | profile          | page-profile.php            | login     | [peptide_community_profile]         |
| Sign In           | auth             | page-auth.php               | open      | (custom forms)                      |

Pages auto-created via `inc/page-setup.php` on theme activation.
"Gated" templates call `peptide_starter_require_login()` before `get_header()`.

## Data Flow

### Authentication (v1.5.1)

```
page-auth.php
  ├─ Sign In form  → auth.js AJAX → inc/auth-handlers.php::peptide_starter_ajax_login
  │                                    ├─ Nonce check
  │                                    ├─ Honeypot check (fake success on trip)
  │                                    ├─ Rate-limit check (login bucket, IP+email hash)
  │                                    ├─ wp_signon (all failures → unified message)
  │                                    └─ Success → reset limiter → redirect
  │                                        (unverified users → /profile?verify_required=1)
  │
  └─ Register form → auth.js AJAX → inc/auth-handlers.php::peptide_starter_ajax_register
                                       ├─ Nonce check
                                       ├─ Honeypot check (fake success on trip)
                                       ├─ Rate-limit check (register bucket, IP only)
                                       ├─ Validation (all failures → unified message)
                                       ├─ wp_create_user
                                       ├─ peptide_starter_send_verification_email
                                       │     ├─ Generate 43-char token
                                       │     ├─ Set ps_pending_verification=1,
                                       │     │   ps_verify_token, ps_verify_expires
                                       │     └─ wp_mail with /verify?uid=&token= link
                                       └─ Respond with "check your inbox" (no redirect, no auto-login)

/verify?uid=&token=
  → inc/email-verification.php::peptide_starter_handle_verify_request
     ├─ hash_equals() token match + TTL check
     ├─ Clear verify meta on success
     ├─ wp_set_auth_cookie
     └─ Redirect /profile?verified=1   (failure → /auth?verify_error=1)
```

### Abuse controls (v1.5.1)

```
Every public form submit:
  1. Nonce verify                    → 403-equivalent security error if missing/invalid
  2. Honeypot check                   → Fake-success drop on non-empty hidden field
  3. Peptide_Starter_Rate_Limiter     → Silent generic error on budget exhaustion
  4. Business logic                   → Unified error on any validation failure

Rate-limit state:
  Storage: wp_options transients, key ps_rl_{action}_{hash}
  Hash:    substr( wp_hash( ip . '|' . identifier ), 0, 16 )
  Never stores raw IPs or identifiers.
```

### Front page assembly

```
front-page.php
  ├─ Hero (title, subtitle, search CTA)
  │    └─ [peptide_search] (Peptide Search AI)
  ├─ Research Modules grid (template-parts/module-cards.php)
  ├─ [prautoblogger_posts]  (PRAutoBlogger)
  └─ [peptide_news]         (Peptide News)
```

### Dark mode coordination

```
wp_head inline script        sets html[data-theme] from localStorage → system pref → customizer default
CSS :root vs html[data-theme="dark"]        colour tokens swap
theme.js toggle                              writes localStorage, dispatches 'themechange' DOM event
```

## External Integrations

Shortcodes (plugins), all wrapped in `shortcode_exists()`:

1. **Peptide Search AI** (`[peptide_search]`) — `.psa-*`
2. **Peptide News** (`[peptide_news]`) — `.pn-*`
3. **PRAutoBlogger** (`[prautoblogger_posts]`) — `.prab-*`
4. **Peptide Tools** (`[peptide_tools_calculator]`, `[peptide_tools_protocol_builder]`)
5. **Peptide Tracker** (`[peptide_tracker]`, `[peptide_tracker_subject_log]`)
6. **Peptide Community** (`[peptide_community_profile]`, `[peptide_directory]`)

## Key Architectural Decisions

1. **Classic theme (not FSE)** — stability, plugin compatibility.
2. **Monolithic CSS** — no build step, CSS custom properties for tokens.
3. **Vanilla JavaScript** — no bundler; `node --check` in CI.
4. **Shortcode integration** — plugins decoupled; theme degrades gracefully.
5. **Split `functions.php` into `inc/` modules** — every file < 300 lines.
6. **Self-contained auth hardening (v1.5.1 / ADR-0001)** — rate limiter,
   honeypots, email verification all live in the theme. No plugin
   dependencies, no paid services. Swappable via two small interfaces
   (`Peptide_Starter_Rate_Limiter`, `peptide_starter_require_login`).
7. **Transient-backed rate limits** — auto-expire, no custom table, no
   uninstall cleanup. If the site ever goes multi-server, swap to the
   shared object cache without caller changes.

## Security Summary

- CSRF nonces on every form (auth, newsletter, contact, mail-test, unsubscribe).
- Unified error messages on login + registration (no enumeration).
- Registration validation does **not** short-circuit — all checks
  evaluate unconditionally so response timing can't distinguish which
  one failed (PSEC-009, v1.5.2).
- Rate limiting on login / register / contact / newsletter / verify-resend.
- Honeypots on the four public forms (login, register, contact, newsletter).
- `require_login()` gate on every template that owns user data.
- Email verification required for write access to user-data tools.
- Existing accounts (created before v1.5.2) are grandfathered as
  verified. Verification enforces only on registrations from v1.5.2
  onward (PSEC-008, v1.5.2).
- CSV export formula-injection safe.
- All input sanitized at the boundary; all output escaped.
- Login redirect validated with `wp_validate_redirect`.
- Rate-limit storage keys are hashed — no raw IPs persisted.
- Contact sender names reject header-injection characters before `wp_mail`.

### Client-IP trust model (PSEC-007, v1.5.2)

peptiderepo.com sits behind Cloudflare. The rate limiter keys on client
IP, so the source of that IP matters for correctness of every abuse
control in the theme.

`peptide_starter_get_client_ip()` trusts in this priority order:

1. **`HTTP_CF_CONNECTING_IP`** — only when `REMOTE_ADDR` is itself
   inside a published Cloudflare edge range (see `inc/cloudflare-ips.php`).
   Direct-to-origin connections that bypass Cloudflare cannot forge this
   header because their peer IP is not a CF edge.
2. **`HTTP_X_FORWARDED_FOR`** — ignored by default. Opt in via
   `add_filter( 'peptide_starter_trust_xff', '__return_true' )` only
   when a known, trusted non-CF proxy fronts the origin.
3. **`REMOTE_ADDR`** — always trusted as the final fallback. Cannot be
   client-controlled at the TCP layer.

`inc/cloudflare-ips.php` holds a static snapshot of CF ranges. Refresh
quarterly. Override via `peptide_starter_cloudflare_ip_ranges` filter
for out-of-band updates.

## Version

**Current:** v1.5.2 (2026-04-14)

Bump `style.css` header line 7 and `PEPTIDE_STARTER_VERSION` in
`functions.php` together on release.
