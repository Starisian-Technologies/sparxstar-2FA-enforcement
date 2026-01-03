#!/bin/bash
# Script to set up the testing environment and install dependencies

echo "==================================="
echo "Sparxstar 2FA Enforcement - Test Setup"
echo "==================================="
echo ""

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo "Error: Composer is not installed."
    echo "Please install Composer from https://getcomposer.org/"
    exit 1
fi

echo "Installing Composer dependencies..."
composer install --prefer-dist --no-progress --no-interaction

if [ $? -eq 0 ]; then
    echo ""
    echo "✓ Dependencies installed successfully!"
    echo ""
    echo "Available test commands:"
    echo "  composer test              - Run all tests"
    echo "  composer test:unit         - Run unit tests only"
    echo "  composer test:integration  - Run integration tests only"
    echo "  composer test:coverage     - Run tests with coverage report"
    echo "  composer lint              - Run code style checks"
    echo "  composer lint:fix          - Fix code style issues"
    echo ""
    echo "Or use the convenience scripts:"
    echo "  ./bin/run-tests.sh         - Run all tests"
    echo "  ./bin/run-tests.sh unit    - Run unit tests"
    echo "  ./bin/run-tests.sh integration - Run integration tests"
    echo "  ./bin/run-tests.sh coverage    - Generate coverage report"
    echo ""
else
    echo ""
    echo "✗ Error installing dependencies"
    exit 1
fi
