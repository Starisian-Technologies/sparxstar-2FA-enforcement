# Plugin Testing Instructions

## About This Repository

This repository contains testing infrastructure for the **Sparxstar 2FA Enforcement** plugin, a WordPress mu-plugin (must-use plugin) that enforces two-factor authentication for all user roles at the point of registration.

## What's Included

This testing setup provides:

- ✅ PHPUnit test framework configuration
- ✅ WordPress function mocking with Brain Monkey
- ✅ Unit and Integration test examples
- ✅ Code quality checks (PHP CodeSniffer)
- ✅ GitHub Actions CI/CD workflow
- ✅ Test helper utilities
- ✅ Comprehensive documentation

## Expected Plugin Structure

When the main plugin file is added, it should work with this test structure. Here's the expected setup:

### Plugin File Location

The main plugin file should be placed in the root directory as:
```
Sparxstar2FAEnforcement.php
```

Or you can organize code in a `src/` directory if needed:
```
src/
└── Sparxstar2FAEnforcement.php
```

### Expected Plugin Header

```php
<?php
/**
 * SPARXSTAR 2FA Enforcement
 *
 * @file        Sparxstar2FAEnforcement.php
 * @package     Starisian\Sparxstar\TwoFactor
 * @version     0.5.0
 * @license     MIT
 * @copyright   Copyright (c) 2026 Starisian Technologies.
 *
 * @wordpress-plugin
 * Plugin Name:       SPARXSTAR 2FA Enforcement
 * Description:       Strictly enforces 2FA with role-based auto-provisioning and CLI recovery. hardened for high-compliance environments.
 * Version:           0.5.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Starisian Technologies (Max Barrett) <support@starisian.com>
 * Author URI:        https://starisian.com
 * Text Domain:       sparxstar-2fa-enforcement
 * License:           MIT
 * License URI:       http://www.gnu.org/licenses/mit.txt
 * Update URI:        https://starisian.com/sparxstar/sparxstar-2fa-enforcement
 */
```

## Updating Tests After Plugin Addition

Once the main plugin file is added, you'll need to:

1. **Update the test files** to test actual functionality instead of placeholders
2. **Add the plugin file to the bootstrap** if needed
3. **Update test assertions** to match actual plugin behavior
4. **Run tests** to ensure everything works

### Example: Writing Tests for the Enforcer Class

The `EnforcerTest.php` file contains production-ready tests for the Enforcer class:
```php
public function test_subscribers_are_locked_to_email_only()
{
    $user = Mockery::mock('WP_User');
    $user->roles = ['subscriber'];
    $user->ID = 123;

    $current_providers = [];
    $result = Sparxstar2FAEnforcement::sparx2FA_provision_and_restrict_methods($current_providers, $user);

    // Expectation: The system returns ONLY email
    $this->assertEquals(['Two_Factor_Email'], $result);
}
```

When adding new functionality:
```php
public function test_new_feature(): void
{
    // Include the plugin file or use autoloading
    // Test your actual plugin functionality
    
    // Test that hooks are registered
    $this->assertTrue(has_action('two_factor_user_enforced', [Sparxstar2FAEnforcement::class, 'sparx2FA_enforce_strict']));
}
```

## Quick Start

### 1. Install Dependencies

```bash
./bin/setup-tests.sh
```

### 2. Add Your Plugin File

Create `Sparxstar2FAEnforcement.php` in the root directory with your plugin code.

### 3. Update Tests

Modify the test files in `tests/Unit/` and `tests/Integration/` to test your actual plugin functionality.

### 4. Run Tests

```bash
composer test
```

Or:

```bash
./bin/run-tests.sh
```

## Test Coverage Goals

The testing infrastructure is designed to cover:

- ✅ Plugin initialization and hooks
- ✅ 2FA enforcement on user registration
- ✅ Multi-site compatibility
- ✅ All user role handling
- ✅ WordPress Two-Factor plugin integration
- ✅ Error handling and edge cases

## Need Help?

See [TESTING.md](TESTING.md) for comprehensive testing documentation, including:
- How to write tests
- Using WordPress mocks
- Running different test suites
- Code coverage reports
- CI/CD integration

## Contributing Tests

When adding new functionality to the plugin:

1. Write tests first (TDD approach recommended)
2. Ensure tests pass locally
3. Check code coverage
4. Run linting: `composer lint`
5. Submit PR with tests included

## License

This testing infrastructure is part of the Sparxstar 2FA Enforcement plugin and is released under the MIT License.
