# SPARXSTAR Enterprise 2FA Enforcement & Recovery (Hardened)

**Version:** 2.0.0

**Type:** WordPress MU-Plugin

**Requires:** PHP 8.2+, WordPress Multisite 6.8+, Official Two-Factor Plugin by WordPress

**Security Model:** Configuration-as-Code

---

## Overview

**SPARXSTAR Enterprise 2FA Enforcement & Recovery (Hardened)** is a strict, role-aware Two-Factor Authentication enforcement layer designed for **high-compliance WordPress Multisite environments**.

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
  If the core Two-Factor plugin is disabled, this MU-plugin exits silently.

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

Users with any role listed in `ENFORCED_ROLES` **must complete 2FA** to log in.

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

### Location (Required)

```text
wp-content/mu-plugins/enterprise-2fa-enforcement.php
```

This plugin **must** be installed as an MU-plugin.

---

### Dependencies

* WordPress Multisite
* Official WordPress Two-Factor plugin
* WP-CLI (for recovery commands)

If the Two-Factor plugin is not active, this MU-plugin **does nothing**.

---

## Configuration

Configuration is done **in code only**, via constants inside the file:

### Roles Allowed to Self-Manage 2FA

```php
private const SELF_MANAGED_ROLES = [
	'administrator',
	'editor',
	'author',
];
```

These users may configure TOTP, WebAuthn, or hardware keys.

---

### Roles That Must Use 2FA

```php
private const ENFORCED_ROLES = [
	'administrator',
	'editor',
	'author',
	'subscriber',
	'contributor',
	'customer',
	'shop_manager',
];
```

Any user with one of these roles is **always enforced**.

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
wp enterprise-2fa bypass admin --minutes=20
```

Creates a **time-boxed bypass** for the specified user.

---

### Revoke a Bypass

```bash
wp enterprise-2fa secure admin
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

## Installation

1. Download the plugin file
2. Upload to `wp-content/mu-plugins/` directory
3. The plugin will automatically activate (mu-plugins auto-load)

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

Copyright (c) 2025-2026 Starisian Technologies. SPARXSTAR and Starisian Technologies are trademarks of Starisian Technologies.

WordPress is a trademark of Wordress.  Mercator and Human Made are trademarks of Human Made. Starisian Technologies is in now was affiliated with WordPress or Human Made.

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
