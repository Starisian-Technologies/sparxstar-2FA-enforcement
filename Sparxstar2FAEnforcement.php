<?php
/**
 * SPARXSTAR 2FA Enforcement
 * 
 * @file 		Sparxstar2FAEnforcement.php
 * @package 	Starisian\Sparxstar\TwoFactor
 * @version		0.5.0
 * @license		MIT
 * @copyright	Copyright (c) 2026 Starisian Technologies.
 *
 * @wordpress-plugin
 * Plugin Name: 	  SPARXSTAR 2FA Enforcement
 * Description: 	  Strictly enforces 2FA with role-based auto-provisioning and CLI recovery. hardened for high-compliance environments.
 * Version: 		  0.5.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author: 			  Starisian Technologies (Max Barrett) <support@starisian.com>
 * Author URI: 		  https://starisian.com
 * Text Domain:       sparxstar-2fa-enforcement
 * License:           MIT
 * License URI:       http://www.gnu.org/licenses/mit.txt
 * Update URI:        https://starisian.com/sparxstar/sparxstar-2fa-enforcement
 */

declare(strict_types=1);

namespace Starisian\Sparxstar\TwoFactor;

use WP_User;
use WP_CLI;
use Two_Factor_Core;
use Two_Factor_Email;
use function add_action;
use function add_filter;
use function get_user_meta;
use function update_user_meta;
use function delete_user_meta;
use function get_user_by;
use function is_super_admin;
use function array_intersect;
use function in_array;
use function class_exists;
use function sprintf;
use function time;
use function defined;
use function _e;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class Sparxstar2FAEnforcement
 * Handles strict enforcement, auto-provisioning, and emergency bypass.
 */
final class Sparxstar2FAEnforcement
{

	private const SPARX_2FA_BYPASS_META_KEY = '_enterprise_2fa_bypass_expires';

	/**
	 * Roles allowed to configure their own 2FA (TOTP/Keys).
	 * All other roles will be strictly locked to Email Only.
	 */
	private const SPARX_2FA_SELF_MANAGED_ROLES = [
		'administrator',
		'editor',
		'author',
		// 'super_admin' is handled implicitly in Multisite, but good to be explicit if needed.
	];

	/**
	 * Roles that MUST use 2FA.
	 * Currently set to ALL, but can be filtered if you need a "Service Account" exemption.
	 * @var SPARX_2FA_ENFORCED_ROLES
	 */
	private const SPARX_2FA_ENFORCED_ROLES = [
		'administrator',
		'editor',
		'author',
		'subscriber',
		'contributor',
		'customer', // WooCommerce support
		'shop_manager',
	];

	/**
	 * Initializes the plugin.
	 *
	 * @return void
	 */
	public static function sparx2FA_init(): void
	{
		// SAFETY: Do not run if the core 2FA plugin is missing.
		if (!class_exists("\Two_Factor_Core")) {
			add_action('admin_notices', [self::class, 'sparx2FA_admin_notice']);
			return;
		}

		// 1. Enforce 2FA (with bypass check).
		add_filter('two_factor_user_enforced', [self::class, 'sparx2FA_enforce_strict'], 10, 2);

		// 2. Register Email Provider (Scoped).
		add_filter('two_factor_providers', [self::class, 'sparx2FA_register_email_provider']);

		// 3. Auto-provisioning & Method Locking.
		add_filter('two_factor_enabled_providers_for_user', [self::class, 'sparx2FA_provision_and_restrict_methods'], 10, 2);

		// 4. Prioritization (Prefer hardware/app over email).
		add_filter('two_factor_primary_provider_for_user', [self::class, 'sparx2FA_prioritize_strongest_method'], 10, 2);

		// 5. Register CLI commands.
		if (defined('WP_CLI') && WP_CLI) {
			\WP_CLI::add_command('enterprise-2fa', Sparxstar2FAEnforcement::class);
		}
	}

	/**
	 * Strictly enforces 2FA.
	 * 
	 * @param bool    $enforced Current enforcement status.
	 * @param WP_User $user     The user object.
	 * @return bool True to enforce, False to allow bypass.
	 */
	public static function sparx2FA_enforce_strict(bool $enforced, \WP_User $user): bool
	{
		// 1. Global Kill Switch via wp-config.php (Panic Button)
		if (defined('ENTERPRISE_2FA_DISABLE_ALL') && ENTERPRISE_2FA_DISABLE_ALL) {
			return false;
		}

		// 2. Check for Valid Emergency Bypass
		$bypass_expires = (int) get_user_meta($user->ID, self::SPARX_2FA_BYPASS_META_KEY, true);
		if ($bypass_expires > time()) {
			return false; // Valid bypass active
		}

		// Cleanup expired keys
		if ($bypass_expires > 0) {
			delete_user_meta($user->ID, self::SPARX_2FA_BYPASS_META_KEY);
		}

		// 3. Strict Role Check
		// If user has ANY of the enforced roles, require 2FA.
		$user_roles = (array) $user->roles;
		if (array_intersect(self::SPARX_2FA_ENFORCED_ROLES, $user_roles) || is_super_admin($user->ID)) {
			return true;
		}

		// Default to existing state if role is not strictly enforced (e.g. custom 3rd party roles)
		return $enforced;
	}

	/**
	 * Ensures the Email provider class is registered.
	 * 
	 * @param array $providers
	 * @return array
	 */
	public static function sparx2FA_register_email_provider(array $providers): array
	{
		// Only register if the class exists (Plugin active) and not already present.
		if (!isset($providers['Two_Factor_Email']) && class_exists('Two_Factor_Email')) {
			$providers['Two_Factor_Email'] = 'Two_Factor_Email';
		}
		return $providers;
	}

	/**
	 * The Core Logic: Auto-provisions Email for compliance, but restricts advanced settings based on role.
	 *
	 * @param array $enabled_providers Currently enabled providers.
	 * @param int   $user_id           The user ID.
	 * @return array Modified providers.
	 */
	public static function sparx2FA_provision_and_restrict_methods(array $enabled_providers, $user_id): array
	{
		$user = get_userdata($user_id);
		if (!$user) {
			return $enabled_providers;
		}

		$is_privileged = self::sparx2FA_is_privileged_user($user);

		// SCENARIO 1: Frontend / Low-Privilege User
		// Requirement: "Frontend users have 2FA but only via emailed one time codes"
		if (!$is_privileged) {
			// STRICT: Wipe other methods. They are not allowed to use TOTP/FIDO as they have no UI to manage it.
			return ['Two_Factor_Email'];
		}

		// SCENARIO 2: Privileged User (Admin/Editor)
		// Requirement: Can use any method, but MUST have at least one.

		// Check for strong methods
		$has_strong_method = !empty(array_intersect(
			['Two_Factor_Totp', 'Two_Factor_FIDO_U2F', 'Two_Factor_WebAuthn'],
			$enabled_providers
		));

		// If they have a strong method, we trust their setup.
		if ($has_strong_method) {
			return $enabled_providers;
		}

		// If they have NO method or only Dummy method, force Email so they are not locked out.
		// This solves the "Chicken and Egg" setup problem.
		if (empty($enabled_providers) || !in_array('Two_Factor_Email', $enabled_providers, true)) {
			$enabled_providers[] = 'Two_Factor_Email';
		}

		return $enabled_providers;
	}

	/**
	 * Prioritizes providers: WebAuthn > TOTP > Email.
	 * @param string $primary_provider Primary provider.
	 * @param int    $user_id          The user ID.
	 * @return string
	 */
	public static function sparx2FA_prioritize_strongest_method(string $primary_provider, $user_id): string
	{
		$user = get_userdata($user_id);
		if (!$user) {
			return $primary_provider;
		}

		$enabled = \Two_Factor_Core::get_enabled_providers_for_user($user->ID);

		// Security Hierarchy
		$hierarchy = [
			'Two_Factor_WebAuthn',
			'Two_Factor_FIDO_U2F',
			'Two_Factor_Totp',
			'Two_Factor_Email',
		];

		foreach ($hierarchy as $method) {
			if (in_array($method, $enabled, true)) {
				return $method;
			}
		}

		return $primary_provider;
	}

	/**
	 * strict check for "Management" capability based on roles.
	 * Replaces the loose 'edit_posts' check.
	 * @return bool
	 */
	private static function sparx2FA_is_privileged_user(\WP_User $user): bool
	{
		return !empty(array_intersect(self::SPARX_2FA_SELF_MANAGED_ROLES, (array) $user->roles)) || is_super_admin($user->ID);
	}

	/**
	 * Bypass 2FA for a specific user for a limited time.
	 *
	 * ## OPTIONS
	 *
	 * <user>
	 * : The user login, email, or ID.
	 *
	 * [--minutes=<minutes>]
	 * : How long the bypass should last. Default: 15.
	 *
	 * ## EXAMPLES
	 *
	 *     wp enterprise-2fa bypass admin --minutes=20
	 * @return void
	 */
	public function sparx2FA_bypass(array $args, array $assoc_args): void
	{
		$user_fetch = $args[0];
		$user = get_user_by('login', $user_fetch) ?: get_user_by('email', $user_fetch) ?: get_user_by('id', $user_fetch);

		if (!$user) {
			\WP_CLI::error("User '$user_fetch' not found.");
		}

		$minutes = (int) ($assoc_args['minutes'] ?? 15);
		$expiry = time() + ($minutes * 60);

		update_user_meta($user->ID, '_enterprise_2fa_bypass_expires', $expiry);

		\WP_CLI::success(sprintf("Bypass active for '%s' for %d minutes.", $user->user_login, $minutes));
	}

	/**
	 * Secure a user (revoke bypass).
	 *
	 * ## OPTIONS
	 *
	 * <user>
	 * : The user login, email, or ID.
	 * @return void
	 */
	public function sparx2FA_secure(array $args): void
	{
		$user_fetch = $args[0];
		$user = get_user_by('login', $user_fetch) ?: get_user_by('email', $user_fetch) ?: get_user_by('id', $user_fetch);

		if (!$user) {
			\WP_CLI::error("User '$user_fetch' not found.");
		}

		delete_user_meta($user->ID, '_enterprise_2fa_bypass_expires');
		\WP_CLI::success("Bypass revoked for '{$user->user_login}'.");
	}

	/**
	 * Verifies email configuration by sending a test email.
	 *
	 * ## OPTIONS
	 *
	 * <user>
	 * : The user login, email, or ID to send to.
	 *
	 * ## EXAMPLES
	 *
	 *     wp enterprise-2fa test-email admin
	 * 
	 * @return void
	 */
	public function sparx2FA_test_email(array $args): void
	{
		$user_fetch = $args[0];
		$user = get_user_by('login', $user_fetch) ?: get_user_by('email', $user_fetch) ?: get_user_by('id', $user_fetch);

		if (!$user) {
			\WP_CLI::error("User '$user_fetch' not found.");
		}

		\WP_CLI::log("Sending test email to {$user->user_email}...");

		// Use global wp_mail to avoid namespace issues if not imported
		$result = \wp_mail(
			$user->user_email,
			'Sparxstar 2FA Verification',
			'This is a test email to verify that your server is correctly configured to send 2FA codes.'
		);

		if ($result) {
			\WP_CLI::success("Email sent successfully!");
		} else {
			\WP_CLI::error("Email failed to send. Please check your server's mail configuration (sendmail, SMTP plugin, etc).");
		}
	}

	/**
	 * Notifies admin that TwoFactor is not installed.
	 * @return void
	 */
	public static function sparx2FA_admin_notice(): void
	{
		?>
		<div class="notice notice-failure is-dismissible">
			<p><?php _e('Please install the WordPress Official <a href="https://wordpress.org/plugins/two-factor" alt="TwoFactor plugin" ref="noopener noreferrer" target="_blank">TwoFactor plugin</a>.', 'sparxstar-2fa-enforcement'); ?>
			</p>
		</div>
		<?php
	}

}

/**
 * Initialize the plugin.
 * Hooking into plugins_loaded ensures that the Two Factor Core plugin is loaded (if active).
 */
add_action('plugins_loaded', [Sparxstar2FAEnforcement::class, 'sparx2FA_init']);
