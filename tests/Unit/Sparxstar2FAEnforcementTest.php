<?php
/************************************************************************
 * STARISIAN TECHNOLOGIES PROPRIETARY AND CONFIDENTIAL
 * __________________
 *
 * Copyright © 2026 Starisian Technologies (Max Barrett)
 * All Rights Reserved.
 *
 * NOTICE: All information, technical concepts, and intellectual property
 * contained herein are, and remain the property of Starisian Technologies.
 * This material is protected by trade secret, copyright, and patent law.
 *
 * Access to this software is granted strictly under the terms of a
 * Starisian Technologies Service Agreement, Commercial License, or
 * Employment Agreement. Dissemination, reproduction, or reverse
 * engineering of this material is strictly forbidden unless prior
 * written permission is obtained from Starisian Technologies.
 ************************************************************************/

/**
 * Unit test for Sparxstar2FAEnforcement class
 *
 * @package Starisian\Sparxstar\TwoFactor\Tests
 */

namespace Starisian\Sparxstar\TwoFactor\Tests\Unit;

use Starisian\Sparxstar\TwoFactor\Sparxstar2FAEnforcement;
use Starisian\Sparxstar\TwoFactor\Tests\Helpers\TestCase;
use Brain\Monkey\Functions;
use Mockery;

/**
 * Test case for Sparxstar2FAEnforcement logic
 */
class Sparxstar2FAEnforcementTest extends TestCase
{
    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Mock the Two Factor Core plugin being active
        Functions\when('class_exists')->alias(function ($class) {
            return in_array($class, ['\Two_Factor_Core', 'Two_Factor_Email']);
        });

        // Mock is_super_admin defaults to false
        Functions\when('is_super_admin')->justReturn(false);
    }

    /**
     * Test that Subscribers (Unprivileged) are FORCED to use Email Only.
     * They should not see TOTP options because they can't access wp-admin to set them up.
     */
    public function test_subscribers_are_locked_to_email_only()
    {
        $user = new \WP_User();
        $user->roles = ['subscriber'];
        $user->ID = 123;
        $user->user_login = 'subscriber';

        // Mock get_userdata
        Functions\when('get_userdata')->justReturn($user);

        // The user currently has NO providers enabled
        $current_providers = [];

        $result = Sparxstar2FAEnforcement::sparx2FA_provision_and_restrict_methods($current_providers, $user->ID);

        // Expectation: The system wipes any other options and returns ONLY email
        $this->assertEquals(['Two_Factor_Email'], $result);
    }

    /**
     * Test that Administrators can have Email auto-provisioned,
     * but are allowed to keep other methods if they set them up.
     */
    public function test_admins_get_email_fallback_but_can_use_others()
    {
        $user = new \WP_User();
        $user->roles = ['administrator'];
        $user->ID = 1;

        // Mock get_userdata
        Functions\when('get_userdata')->justReturn($user);

        // Scenario: Admin has nothing set up yet
        $current_providers = [];

        $result = Sparxstar2FAEnforcement::sparx2FA_provision_and_restrict_methods($current_providers, $user->ID);

        // Expectation: Auto-provision email so they don't get locked out
        $this->assertContains('Two_Factor_Email', $result);

        // Scenario: Admin HAS set up TOTP
        $current_providers_advanced = ['Two_Factor_Totp'];
        $result_advanced = Sparxstar2FAEnforcement::sparx2FA_provision_and_restrict_methods($current_providers_advanced, $user->ID);

        // Expectation: We respect their TOTP setup
        $this->assertContains('Two_Factor_Totp', $result_advanced);
    }

    /**
     * The callback must be registered on a filter the Two Factor plugin applies.
     *
     * Every other test here calls sparx2FA_enforce_strict() directly, which proves
     * the logic but never the wiring. That gap let the plugin ship hooked to
     * 'two_factor_user_enforced', a name absent from the whole of the Two Factor
     * source, so the callback never ran at all.
     *
     * Each name below was read from the plugin's source:
     *   two_factor_is_required_for_user       class-two-factor-core.php:966
     *   two_factor_providers                  two-factor.php:81
     *   two_factor_enabled_providers_for_user two-factor.php:82
     *   two_factor_primary_provider_for_user  class-two-factor-core.php:924
     */
    public function test_init_hooks_filters_the_two_factor_plugin_actually_applies()
    {
        $added = [];
        Functions\when('add_filter')->alias(
            function ($hook, $callback, $priority = 10, $accepted = 1) use (&$added) {
                $added[] = $hook;

                return true;
            }
        );
        Functions\when('add_action')->justReturn(true);

        Sparxstar2FAEnforcement::sparx2FA_init();

        $this->assertContains(
            'two_factor_is_required_for_user',
            $added,
            'Enforcement must hook a filter the Two Factor plugin applies.'
        );
        $this->assertContains('two_factor_providers', $added);
        $this->assertContains('two_factor_enabled_providers_for_user', $added);
        $this->assertContains('two_factor_primary_provider_for_user', $added);

        $this->assertNotContains(
            'two_factor_user_enforced',
            $added,
            'two_factor_user_enforced does not exist in the Two Factor plugin; hooking it is a no-op.'
        );
    }

    /**
     * A user in an enforced role with no usable provider must still be able to log in.
     *
     * Upstream fails such a login outright rather than prompting enrolment
     * (class-two-factor-core.php:953-956 and wp_login() at :979-992), so returning
     * true here would lock the user out, not protect them.
     */
    public function test_enforced_role_without_a_provider_is_not_locked_out()
    {
        $user = new \WP_User();
        $user->roles = ['administrator'];
        $user->ID = 1;

        Functions\when('get_user_meta')->justReturn(false);

        // false = core found no usable provider for this user.
        $should_enforce = Sparxstar2FAEnforcement::sparx2FA_enforce_strict(false, $user);

        $this->assertFalse(
            $should_enforce,
            'Enforcement must not demand a factor the user has no way to supply.'
        );
    }

    /**
     * A customer who has been provisioned a factor is still required to use it.
     *
     * sparx2FA_provision_and_restrict_methods() forces Two_Factor_Email on for
     * non-privileged roles, so core reports a provider and $enforced arrives true.
     */
    public function test_provisioned_customer_is_still_required_to_use_2fa()
    {
        $user = new \WP_User();
        $user->roles = ['customer'];
        $user->ID = 42;

        Functions\when('get_user_meta')->justReturn(false);

        $should_enforce = Sparxstar2FAEnforcement::sparx2FA_enforce_strict(true, $user);

        $this->assertTrue($should_enforce, 'A provisioned customer must still be required to use 2FA.');
    }

    /**
     * The emergency bypass must rescue a locked-out administrator.
     */
    public function test_bypass_rescues_a_locked_out_administrator()
    {
        $user = new \WP_User();
        $user->roles = ['administrator'];
        $user->ID = 1;

        // What `wp enterprise-2fa bypass admin` writes: an expiry in the future.
        Functions\when('get_user_meta')->justReturn(time() + 900);

        $this->assertFalse(
            Sparxstar2FAEnforcement::sparx2FA_enforce_strict(true, $user),
            'An active bypass must drop the second-factor requirement.'
        );
    }


    /**
     * Test the Emergency Bypass Logic.
     */
    public function test_bypass_logic_honors_time_limit()
    {
        $user = new \WP_User();
        $user->roles = ['administrator'];
        $user->ID = 1;
        $user->user_login = 'admin';

        // Scenario 1: Bypass is set and valid (future timestamp)
        Functions\when('get_user_meta')->justReturn(time() + 300); // Expires in 5 mins

        $should_enforce = Sparxstar2FAEnforcement::sparx2FA_enforce_strict(true, $user);
        $this->assertFalse($should_enforce, 'Enforcement should be skipped if bypass is active');

        // Scenario 2: Bypass is expired (past timestamp)
        Functions\when('get_user_meta')->justReturn(time() - 300); // Expired 5 mins ago

        Functions\expect('delete_user_meta')->once(); // Should clean up

        $should_enforce_expired = Sparxstar2FAEnforcement::sparx2FA_enforce_strict(true, $user);
        $this->assertTrue($should_enforce_expired, 'Enforcement should resume if bypass is expired');
    }
    /**
     * The kill switch must disable enforcement for everyone.
     *
     * MUST REMAIN THE LAST TEST IN THIS CLASS. A PHP constant cannot be
     * undefined once set, and PHPUnit runs the methods of a class in
     * declaration order, so defining ENTERPRISE_2FA_DISABLE_ALL here would
     * switch enforcement off for every test declared after it. Process
     * isolation is not an option: @runInSeparateProcess collides with
     * Mockery's generated classes through the shared base TestCase.
     */
    public function test_kill_switch_disables_enforcement()
    {
        if (!defined('ENTERPRISE_2FA_DISABLE_ALL')) {
            define('ENTERPRISE_2FA_DISABLE_ALL', true);
        }

        $user = new \WP_User();
        $user->roles = ['administrator'];
        $user->ID = 1;

        Functions\when('get_user_meta')->justReturn(false);

        $this->assertFalse(
            Sparxstar2FAEnforcement::sparx2FA_enforce_strict(true, $user),
            'ENTERPRISE_2FA_DISABLE_ALL must switch enforcement off even for an enforced role.'
        );
    }
}
