<?php
/**
 * PHPUnit bootstrap file for Sparxstar 2FA Enforcement tests
 *
 * @package StarisianTechnologies\Sparxstar2FA\Tests
 */

// Composer autoloader
$autoloader = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

// Bootstrap Brain Monkey for WordPress function mocking
if (function_exists('Brain\Monkey\setUp')) {
    // Brain Monkey will be set up in each test
}

// Define WordPress constants for testing
if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wordpress/');
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
}

if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
}

if (!defined('WPMU_PLUGIN_DIR')) {
    define('WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins');
}

// Set up WordPress testing environment type
if (!defined('WP_ENVIRONMENT_TYPE')) {
    define('WP_ENVIRONMENT_TYPE', 'testing');
}

// Initialize test helpers
require_once __DIR__ . '/Helpers/TestCase.php';
require_once __DIR__ . '/Helpers/WordPressMocks.php';

echo "Bootstrap complete. Running tests...\n";
