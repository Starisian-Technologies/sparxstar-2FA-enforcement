<?php
/**
 * PHPUnit bootstrap file for SPARXSTAR 2FA Enforcement tests
 *
 * @package Starisian\Sparxstar\TwoFactor\Tests
 */

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

// Composer autoloader
$autoloader = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

// Bootstrap Brain Monkey for WordPress function mocking
if (function_exists('Brain\Monkey\setUp')) {
    // Brain Monkey will be set up in each test
}

// Initialize test helpers
require_once __DIR__ . '/Helpers/TestCase.php';
require_once __DIR__ . '/Helpers/WordPressMocks.php';

// Stub WP classes
if (!class_exists('WP_User')) {
    class WP_User
    {
        public $ID;
        public $roles = [];
        public $user_login;
        public $user_email;
        public function __construct($id = 0, $name = '', $site_id = '')
        {
        }
    }
}
if (!class_exists('WP_Error')) {
    class WP_Error
    {
    }
}

echo "Bootstrap complete. Running tests...\n";
