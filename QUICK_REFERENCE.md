# Quick Reference - Testing Commands

## Setup
```bash
# Install dependencies
./bin/setup-tests.sh

# Or manually
composer install
```

## Running Tests
```bash
# All tests
composer test
./bin/run-tests.sh

# Unit tests only
composer test:unit
./bin/run-tests.sh unit

# Integration tests only
composer test:integration
./bin/run-tests.sh integration

# With coverage
composer test:coverage
./bin/run-tests.sh coverage
```

## Code Quality
```bash
# Check code style
composer lint

# Fix code style
composer lint:fix

# Check WordPress standards
composer lint:check
```

## Direct PHPUnit Usage
```bash
# All tests
./vendor/bin/phpunit

# Specific test file
./vendor/bin/phpunit tests/Unit/EnforcerTest.php

# Specific test method
./vendor/bin/phpunit --filter test_subscribers_are_locked_to_email_only

# Verbose output
./vendor/bin/phpunit --verbose

# Stop on first failure
./vendor/bin/phpunit --stop-on-failure
```

## Test Structure
```
tests/
├── bootstrap.php              # Test initialization
├── Helpers/                   # Helper classes
│   ├── TestCase.php          # Base test class
│   └── WordPressMocks.php    # WordPress function mocks
├── Unit/                      # Unit tests
│   └── EnforcerTest.php      # Enforcer class tests
└── Integration/               # Integration tests (empty)
```

## WordPress Mocking Examples

### Mock User
```php
$user = WordPressMocks::mockUser(1, 'testuser', 'test@example.com', ['subscriber']);
WordPressMocks::mockGetUserdata($user);
```

### Mock Options
```php
WordPressMocks::mockGetOption('option_name', 'option_value');
WordPressMocks::mockUpdateOption();
```

### Mock Multisite
```php
WordPressMocks::mockIsMultisite(true);
WordPressMocks::mockGetBlogOption('value');
```

### Mock User Meta
```php
WordPressMocks::mockGetUserMeta(['key' => 'value']);
WordPressMocks::mockUpdateUserMeta();
```

### Mock Capabilities
```php
WordPressMocks::mockCurrentUserCan(true);
WordPressMocks::mockUserCan(true);
```

## Brain Monkey - Actions & Filters

### Test Actions
```php
use Brain\Monkey\Actions;

// Expect action to fire
Actions\expectDone('my_action')->once()->with($param);

// Check if action fired
$this->assertActionFired('my_action');
```

### Test Filters
```php
use Brain\Monkey\Filters;

// Expect filter application
Filters\expectApplied('my_filter')->once()->andReturn($value);

// Check if filter applied
$this->assertFilterApplied('my_filter');
```

### Mock Functions
```php
use Brain\Monkey\Functions;

// Simple mock
Functions\when('my_function')->justReturn('value');

// With parameters
Functions\expect('update_user_meta')
    ->once()
    ->with(1, 'key', 'value')
    ->andReturn(true);
```

## File Locations

| File | Purpose |
|------|---------|
| `composer.json` | Dependencies and scripts |
| `phpunit.xml` | PHPUnit configuration |
| `phpcs.xml` | Code style configuration |
| `.github/workflows/tests.yml` | CI/CD configuration |
| `TESTING.md` | Full testing documentation |
| `PLUGIN_TESTING_INSTRUCTIONS.md` | Plugin developer guide |

## CI/CD

Tests run automatically on GitHub Actions for:
- PHP versions: 7.4, 8.0, 8.1, 8.2
- Branches: main, develop
- All pull requests

View results: https://github.com/Starisian-Technologies/sparxstar-2FA-enforcement/actions

## Getting Help

1. Check [TESTING.md](TESTING.md) for detailed documentation
2. Review [PLUGIN_TESTING_INSTRUCTIONS.md](PLUGIN_TESTING_INSTRUCTIONS.md) for plugin-specific info
3. Look at example tests in `tests/Unit/` and `tests/Integration/`
4. Open an issue on GitHub

## Common Issues

**Dependencies not installed**: Run `composer install`

**PHPUnit not found**: Use `./vendor/bin/phpunit` not just `phpunit`

**Tests fail after adding plugin**: Update placeholder tests in test files

**Permission denied**: Make scripts executable: `chmod +x bin/*.sh`
