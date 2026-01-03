# Sparxstar 2FA Enforcement

A multi-site aware, WordPress mu-plugin to enforce WordPress Two-Factor, a 2FA plugin, security for all user roles at the point of registration.

## Features

- 🔒 Enforces 2FA for all user roles at registration
- 🌐 Multi-site compatible
- 🔌 Integrates with WordPress Two-Factor plugin
- ⚡ Single-file mu-plugin for easy deployment
- ✅ Comprehensive test coverage

## Installation

1. Download the plugin file
2. Upload to `wp-content/mu-plugins/` directory
3. The plugin will automatically activate (mu-plugins auto-load)

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- WordPress Two-Factor plugin installed and activated

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

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Author

Starisian Technologies
