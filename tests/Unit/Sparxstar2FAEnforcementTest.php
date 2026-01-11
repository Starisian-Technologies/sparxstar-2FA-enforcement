<?php
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
     * Test strict enforcement logic.
     */
    public function test_strict_enforcement_checks()
    {
        $user = new \WP_User();
        $user->roles = ['administrator'];
        $user->ID = 1;

        // Mock get_user_meta to return false (no bypass active)
        Functions\when('get_user_meta')->justReturn(false);

        // Run the filter
        $should_enforce = Sparxstar2FAEnforcement::sparx2FA_enforce_strict(false, $user);

        $this->assertTrue($should_enforce, 'Admin should be strictly enforced to use 2FA');
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
}
