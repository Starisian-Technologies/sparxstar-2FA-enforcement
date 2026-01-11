<img width="1280" height="640" alt="sparxstar-tfa-enforcement" src="https://github.com/user-attachments/assets/99439d34-c32c-4410-bbd1-1bbd1296ed82" />

# SPARXSTAR 2FA Enforcement

**Version:** 0.5.0

**Type:** WordPress MU-Plugin

**Requires:** PHP 7.2+, WordPress Multisite, Official Two-Factor Plugin by WordPress

**Security Model:** Configuration-as-Code

[![CodeQL](https://github.com/Starisian-Technologies/sparxstar-2FA-enforcement/actions/workflows/github-code-scanning/codeql/badge.svg)](https://github.com/Starisian-Technologies/sparxstar-2FA-enforcement/actions/workflows/github-code-scanning/codeql)  [![Copilot code review](https://github.com/Starisian-Technologies/sparxstar-2FA-enforcement/actions/workflows/copilot-pull-request-reviewer/copilot-pull-request-reviewer/badge.svg)](https://github.com/Starisian-Technologies/sparxstar-2FA-enforcement/actions/workflows/copilot-pull-request-reviewer/copilot-pull-request-reviewer)  [![Copilot coding agent](https://github.com/Starisian-Technologies/sparxstar-2FA-enforcement/actions/workflows/copilot-swe-agent/copilot/badge.svg)](https://github.com/Starisian-Technologies/sparxstar-2FA-enforcement/actions/workflows/copilot-swe-agent/copilot) 

[![Release Plugin](https://github.com/Starisian-Technologies/sparxstar-2FA-enforcement/actions/workflows/release.yml/badge.svg)](https://github.com/Starisian-Technologies/sparxstar-2FA-enforcement/actions/workflows/release.yml)

---

## Overview

**SPARXSTAR Enterprise 2FA Enforcement (Hardened)** is a strict, role-aware Two-Factor Authentication enforcement layer designed for **high-compliance WordPress Multisite environments**.

This plugin **does not replace** the official WordPress Two-Factor plugin.
It **extends and hardens it** by enforcing deterministic rules around:

* Who must use 2FA
* Which 2FA methods are allowed by role
* How frontend-only users complete 2FA
* How administrators recover from lockouts safely

It is built to work **cleanly and safely** alongside:

* WordPress Multisite
* Mercator (domain mapping) by Human Made
* Mercator-Redirect by Human Made
* Cross-domain SSO scenarios

---

## Design Principles

* **Configuration as Code**
  No UI settings. All policy is defined in constants.

* **Fail-Safe by Default**
  If the core Two-Factor plugin is disabled, this MU-plugin exits silently (with an admin notice).

* **Strict Role Boundaries**
  No loose capability checks (`edit_posts` etc.).
  Only explicit role allowlists are trusted.

* **Frontend-Safe Enforcement**
  Users without wp-admin access are never forced into unusable 2FA methods.

* **Emergency Recovery Without UI Exposure**
  Recovery is CLI-only, time-boxed, and auditable.

---

## What This Plugin Does

### 1. Enforces 2FA by Role

Users with any role listed in `SPARX_2FA_ENFORCED_ROLES` **must complete 2FA** to log in.
Super Admins are also strictly enforced across the network.

Example enforced roles:

* administrator
* editor
* author
* contributor
* subscriber
* customer
* shop_manager

---

### 2. Role-Based 2FA Method Control

| User Type                                   | Allowed Methods                  |
| ------------------------------------------- | -------------------------------- |
| Admin / Editor / Author                     | WebAuthn, TOTP, Email (fallback) |
| Frontend Users (Subscriber, Customer, etc.) | **Email One-Time Codes ONLY**    |

This prevents frontend users from being locked out by methods they cannot configure.

---

### 3. Automatic Safe Provisioning

* Privileged users must have **at least one valid 2FA method**
* Email is auto-added only when required to avoid lockout
* Stronger methods are always preferred when present

---

### 4. Emergency Recovery (CLI-Only)

Administrators can temporarily bypass 2FA for a specific user via WP-CLI.

* Time-limited (default 15 minutes)
* No UI surface
* No permanent bypass states

---

## Installation

This plugin can be installed as a **Must-Use (MU) Plugin** (recommended for strict enforcement) or a **Regular Plugin**.

### Option 1: Must-Use Plugin (Recommended)

1. Download `sparxstar-2fa-enforcement.zip` from the [Releases page](https://github.com/Starisian-Technologies/sparxstar-2FA-enforcement/releases).
2. Extract the zip file.
3. Upload `Sparxstar2FAEnforcement.php` directly to your `wp-content/mu-plugins/` directory. 
   *(Note: If the `mu-plugins` directory does not exist, you must create it).*
4. The plugin is automatically active for all sites.

### Option 2: Regular Plugin

1. Download `sparxstar-2fa-enforcement.zip` from the [Releases page](https://github.com/Starisian-Technologies/sparxstar-2FA-enforcement/releases).
2. In your WordPress Admin, go to **Plugins > Add New > Upload Plugin**.
3. Upload the zip file and click **Install Now**.
4. **Network Activate** (for Multisite) or **Activate** the plugin.

### Dependencies

* **WordPress 5.2+**
* **PHP 7.2+**
* **[Two-Factor](https://wordpress.org/plugins/two-factor/)** plugin (Required)
  * This plugin extends the official Two-Factor plugin. If the base plugin is missing, this enforcement layer will exit safely and display an admin notice.

---

## Configuration

Configuration is done **in code only**, via constants inside the file:

### Roles Allowed to Self-Manage 2FA

```php
private const SPARX_2FA_SELF_MANAGED_ROLES = [
	'administrator',
	'editor',
	'author',
];
```

These users may configure TOTP, WebAuthn, or hardware keys.

---

### Roles That Must Use 2FA

```php
private const SPARX_2FA_ENFORCED_ROLES = [
	'administrator',
	'editor',
	'author',
	'subscriber',
	'contributor',
	'customer',
	'shop_manager',
];
```

Any user with one of these roles (or Super Admin) is **always enforced**.

---

### Global Kill Switch (Emergency Only)

Add to `wp-config.php`:

```php
define( 'ENTERPRISE_2FA_DISABLE_ALL', true );
```

This disables enforcement **without removing the plugin**.

---

## WP-CLI Commands

### Temporarily Bypass 2FA

```bash
wp enterprise-2fa sparx2FA_bypass admin --minutes=20
```

Creates a **time-boxed bypass** for the specified user.

---

### Revoke a Bypass

```bash
wp enterprise-2fa sparx2FA_secure admin
```

Immediately re-enforces 2FA.

---

## Multisite & Domain Mapping Compatibility

This plugin is **fully compatible** with:

* WordPress Multisite authentication
* Mercator domain mapping
* Mercator-Redirect canonical redirects

### Important Notes

* 2FA is enforced **before** authentication cookies are issued
* Once 2FA succeeds, WordPress issues normal auth cookies
* Mercator ensures those cookies are valid across mapped domains
* No cookie logic is overridden by this plugin

Result:
**One login, one 2FA challenge, seamless cross-site access**

---

## Security Characteristics

* No UI attack surface
* No capability inference
* No cookie manipulation
* No redirect logic
* No fatal error paths
* No persistent bypass states

This plugin is suitable for:

* Regulated environments
* Education platforms
* Contributor networks
* Enterprise multisite installations

---

## Development

### Testing

This repository includes comprehensive testing infrastructure:

```bash
# Install test dependencies
./bin/setup-tests.sh

# Run all tests
composer test

# Run unit tests only
composer test:unit

# Run integration tests only
composer test:integration

# Generate coverage report
composer test:coverage
```

See [TESTING.md](TESTING.md) for detailed testing documentation.

### Code Quality

```bash
# Check code style
composer lint

# Fix code style issues
composer lint:fix
```

## Documentation

- [Testing Guide](TESTING.md) - Comprehensive guide for running and writing tests
- [Plugin Testing Instructions](PLUGIN_TESTING_INSTRUCTIONS.md) - Instructions for plugin developers
- [Quick Reference](QUICK_REFERENCE.md) - Quick command reference for testing

## Contributing

Contributions are welcome! Please ensure:

1. All tests pass
2. Code follows WordPress Coding Standards
3. New features include tests
4. Documentation is updated

---

## License

MIT License - see [LICENSE](LICENSE) file for details.

This plugin is intended for controlled deployment environments.

Copyright (c) 2025-2026 Starisian Technologies. 

SPARXSTAR and Starisian Technologies are trademarks of Starisian Technologies. WordPress is a trademark of WordPress Inc.  
Starisian Technologies is in no way affiliated with WordPress.

---

## Support & Maintenance

This plugin intentionally exposes:

* **No settings UI**
* **No public API**
* **No extension points**

All changes should be made via:

* Code review
* Version control
* Controlled deployment

---

**Status:** Production-Ready

**Security Posture:** Hardened

**Intended Audience:** Architects, Security Engineers, Platform Owners
