#!/bin/bash
# Script to run tests for the Sparxstar 2FA Enforcement plugin

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Default test suite
TEST_SUITE="all"

# Check if an argument is provided
if [ ! -z "$1" ]; then
    TEST_SUITE="$1"
fi

echo "==================================="
echo "Sparxstar 2FA Enforcement - Test Runner"
echo "==================================="
echo ""

# Check if vendor directory exists
if [ ! -d "vendor" ]; then
    echo -e "${RED}Error: Dependencies not installed${NC}"
    echo "Please run: ./bin/setup-tests.sh"
    exit 1
fi

# Run tests based on the suite
case "$TEST_SUITE" in
    "unit")
        echo -e "${YELLOW}Running unit tests...${NC}"
        ./vendor/bin/phpunit --testsuite unit
        ;;
    "integration")
        echo -e "${YELLOW}Running integration tests...${NC}"
        ./vendor/bin/phpunit --testsuite integration
        ;;
    "coverage")
        echo -e "${YELLOW}Running tests with coverage...${NC}"
        ./vendor/bin/phpunit --coverage-html coverage --coverage-text
        echo ""
        echo -e "${GREEN}Coverage report generated in ./coverage directory${NC}"
        ;;
    "all"|*)
        echo -e "${YELLOW}Running all tests...${NC}"
        ./vendor/bin/phpunit
        ;;
esac

# Check test result
if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}✓ Tests passed!${NC}"
    exit 0
else
    echo ""
    echo -e "${RED}✗ Tests failed${NC}"
    exit 1
fi
