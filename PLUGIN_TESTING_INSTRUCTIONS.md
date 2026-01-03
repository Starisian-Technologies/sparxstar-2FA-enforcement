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
sparxstar-2fa-enforcement.php
```

Or you can organize code in a `src/` directory if needed:
```
src/
└── Plugin.php
```

### Expected Plugin Header

```php
<?php
/**
 * Plugin Name: Sparxstar 2FA Enforcement
 * Description: A multi-site aware, WordPress mu-plugin to enforce WordPress Two-Factor, a 2FA plugin, security for all user roles at the point of registration.
 * Version: 1.0.0
 * Author: Starisian Technologies
 * License: MIT
 * Text Domain: sparxstar-2fa-enforcement
 */
```

## Updating Tests After Plugin Addition

Once the main plugin file is added, you'll need to:

1. **Update the test files** to test actual functionality instead of placeholders
2. **Add the plugin file to the bootstrap** if needed
3. **Update test assertions** to match actual plugin behavior
4. **Run tests** to ensure everything works

### Example: Updating PluginInitializationTest.php

Replace placeholder tests like:
```php
public function testPluginHooksAreRegistered(): void
{
    $this->assertTrue(true, 'Placeholder test');
}
```

With actual tests:
```php
public function testPluginHooksAreRegistered(): void
{
    // Include the plugin file
    require_once __DIR__ . '/../../sparxstar-2fa-enforcement.php';
    
    // Test that hooks are registered
    $this->assertTrue(has_action('user_register', 'sparxstar_enforce_2fa'));
}
```

## Quick Start

### 1. Install Dependencies

```bash
./bin/setup-tests.sh
```

### 2. Add Your Plugin File

Create `sparxstar-2fa-enforcement.php` in the root directory with your plugin code.

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
