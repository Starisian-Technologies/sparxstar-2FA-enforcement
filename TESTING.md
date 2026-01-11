# Testing Guide

This document provides information on how to test the Sparxstar 2FA Enforcement plugin.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Setup](#setup)
- [Running Tests](#running-tests)
- [Test Structure](#test-structure)
- [Writing Tests](#writing-tests)
- [Continuous Integration](#continuous-integration)
- [Code Coverage](#code-coverage)

## Prerequisites

Before running tests, ensure you have the following installed:

- PHP 7.4 or higher
- Composer
- Git

## Setup

### Initial Setup

1. Clone the repository (if you haven't already):
   ```bash
   git clone https://github.com/Starisian-Technologies/sparxstar-2FA-enforcement.git
   cd sparxstar-2FA-enforcement
   ```

2. Run the setup script to install test dependencies:
   ```bash
   ./bin/setup-tests.sh
   ```

   Or manually install with Composer:
   ```bash
   composer install
   ```

## Running Tests

### Using Composer Scripts

The easiest way to run tests is using the Composer scripts defined in `composer.json`:

```bash
# Run all tests
composer test

# Run only unit tests
composer test:unit

# Run only integration tests
composer test:integration

# Run tests with coverage report
composer test:coverage
```

### Using Test Runner Scripts

Alternatively, use the provided shell scripts:

```bash
# Run all tests
./bin/run-tests.sh

# Run unit tests only
./bin/run-tests.sh unit

# Run integration tests only
./bin/run-tests.sh integration

# Generate coverage report
./bin/run-tests.sh coverage
```

### Using PHPUnit Directly

For more control, you can run PHPUnit directly:

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test file
./vendor/bin/phpunit tests/Unit/EnforcerTest.php

# Run specific test method
./vendor/bin/phpunit --filter test_subscribers_are_locked_to_email_only

# Run with verbose output
./vendor/bin/phpunit --verbose
```

## Test Structure

The test suite is organized into the following directories:

```
tests/
├── bootstrap.php                    # Test bootstrap file
├── Helpers/                         # Test helper classes
│   ├── TestCase.php                # Base test case class
│   └── WordPressMocks.php          # WordPress mocking utilities
├── Unit/                            # Unit tests
│   └── EnforcerTest.php            # Enforcer class tests
└── Integration/                     # Integration tests (empty - ready for future tests)
```

### Test Types

- **Unit Tests**: Test individual functions and methods in isolation (EnforcerTest)
- **Integration Tests**: Test how different parts of the plugin work together (to be added)

## Writing Tests

### Creating a New Test

1. Create a new test file in the appropriate directory (`tests/Unit` or `tests/Integration`)
2. Extend the base `TestCase` class
3. Use the `WordPressMocks` helper for mocking WordPress functions

Example:

```php
<?php

namespace Starisian\Sparxstar\TwoFactor\Tests\Unit;

use Starisian\Sparxstar\TwoFactor\Tests\Helpers\TestCase;
use Starisian\Sparxstar\TwoFactor\Tests\Helpers\WordPressMocks;
use Brain\Monkey\Functions;

class MyFeatureTest extends TestCase
{
    public function testMyFeature(): void
    {
        // Mock WordPress functions
        WordPressMocks::mockGetOption('my_option', 'my_value');
        
        // Your test code here
        $this->assertTrue(true);
    }
}
```

### Using WordPress Mocks

The `WordPressMocks` helper class provides convenient methods for mocking common WordPress functions:

```php
// Mock user functions
WordPressMocks::mockUser($id, $login, $email, $roles);
WordPressMocks::mockGetCurrentUserId($user_id);
WordPressMocks::mockGetUserBy($user);
WordPressMocks::mockGetUserdata($user);

// Mock multisite functions
WordPressMocks::mockIsMultisite(true);
WordPressMocks::mockGetBlogOption($value);

// Mock option functions
WordPressMocks::mockGetOption($option_name, $value);
WordPressMocks::mockUpdateOption();

// Mock capability functions
WordPressMocks::mockCurrentUserCan(true);
WordPressMocks::mockUserCan(true);

// Mock user meta functions
WordPressMocks::mockGetUserMeta($value);
WordPressMocks::mockUpdateUserMeta();
```

### Testing WordPress Hooks

You can test WordPress actions and filters using Brain Monkey:

```php
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;

// Expect an action to be fired
Actions\expectDone('my_custom_action')->once()->with($user_id);

// Expect a filter to be applied
Filters\expectApplied('my_custom_filter')->once()->andReturn($value);

// Check if action was fired
$this->assertActionFired('my_custom_action');

// Check if filter was applied
$this->assertFilterApplied('my_custom_filter');
```

## Code Quality

### Linting

Run PHP CodeSniffer to check code style:

```bash
# Check code style
composer lint

# Automatically fix code style issues
composer lint:fix

# Check WordPress coding standards
composer lint:check
```

### Code Style Standards

This project follows the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/).

## Continuous Integration

This project uses GitHub Actions for continuous integration. Tests are automatically run on:

- Every push to `main` and `develop` branches
- Every pull request to `main` and `develop` branches

The CI pipeline runs tests on multiple PHP versions (7.4, 8.0, 8.1, 8.2) to ensure compatibility.

### Viewing CI Results

1. Go to the [Actions tab](https://github.com/Starisian-Technologies/sparxstar-2FA-enforcement/actions) in the GitHub repository
2. Click on a workflow run to see detailed results
3. Check the test output and any failed tests

## Code Coverage

### Generating Coverage Reports

Generate an HTML coverage report:

```bash
composer test:coverage
```

The report will be generated in the `coverage/` directory. Open `coverage/index.html` in your browser to view it.

### Coverage on CI

Code coverage is automatically generated on CI for PHP 8.1 and uploaded to Codecov (if configured).

## Troubleshooting

### Common Issues

#### Dependencies Not Installed

**Error**: `vendor` directory not found

**Solution**: Run `composer install` or `./bin/setup-tests.sh`

#### PHPUnit Not Found

**Error**: `phpunit: command not found`

**Solution**: Make sure you're using `./vendor/bin/phpunit` or Composer scripts

#### Brain Monkey Errors

**Error**: WordPress function not mocked

**Solution**: Add the appropriate mock in your test setup or use `WordPressMocks` helper

## Additional Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Brain Monkey Documentation](https://brain-wp.github.io/BrainMonkey/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)

## Support

If you encounter any issues with testing, please:

1. Check this documentation
2. Review existing test files for examples
3. Open an issue on GitHub with details about the problem
