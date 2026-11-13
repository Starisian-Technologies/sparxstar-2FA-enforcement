# Integration tests

This directory is intentionally present while empty.

`phpunit.xml` declares an `integration` test suite pointing here, and the
Tests workflow runs `composer test:integration` on every push and pull
request. PHPUnit exits 2 — a failure — when a declared suite's directory does
not exist, but exits 0 with "No tests executed!" when it exists and is empty.

So deleting this directory turns the whole CI job red without a single test
having failed. Add integration tests here; do not remove it.
