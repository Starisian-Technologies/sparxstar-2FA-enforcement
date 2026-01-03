<?php
/**
 * Unit test for Enforcer class
 *
 * @package Enterprise\Security\TwoFactor\Tests
 */

namespace Enterprise\Security\TwoFactor\Tests\Unit;

use Enterprise\Security\TwoFactor\Enforcer;
use Enterprise\Security\TwoFactor\Tests\Helpers\TestCase;
use Brain\Monkey\Functions;
use Mockery;

/**
 * Test case for Enforcer logic
 */
class EnforcerTest extends TestCase
{
    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Mock the Two Factor Core plugin being active
        Functions\when('class_exists')->with('\Two_Factor_Core')->justReturn(true);
        Functions\when('class_exists')->with('Two_Factor_Email')->justReturn(true);
    }

    /**
     * Test that Subscribers (Unprivileged) are FORCED to use Email Only.
     * They should not see TOTP options because they can't access wp-admin to set them up.
     */
    public function test_subscribers_are_locked_to_email_only()
    {
        $user = Mockery::mock('WP_User');
        $user->roles = ['subscriber'];
        $user->ID = 123;

        // The user currently has NO providers enabled
        $current_providers = [];

        $result = Enforcer::provision_and_restrict_methods($current_providers, $user);

        // Expectation: The system wipes any other options and returns ONLY email
        $this->assertEquals(['Two_Factor_Email'], $result);
    }

    /**
     * Test that Administrators can have Email auto-provisioned,
     * but are allowed to keep other methods if they set them up.
     */
    public function test_admins_get_email_fallback_but_can_use_others()
    {
        $user = Mockery::mock('WP_User');
        $user->roles = ['administrator'];
        $user->ID = 1;

        // Scenario: Admin has nothing set up yet
        $current_providers = [];

        $result = Enforcer::provision_and_restrict_methods($current_providers, $user);

        // Expectation: Auto-provision email so they don't get locked out
        $this->assertContains('Two_Factor_Email', $result);

        // Scenario: Admin HAS set up TOTP
        $current_providers_advanced = ['Two_Factor_Totp'];
        $result_advanced = Enforcer::provision_and_restrict_methods($current_providers_advanced, $user);

        // Expectation: We respect their TOTP setup
        $this->assertContains('Two_Factor_Totp', $result_advanced);
    }

    /**
     * Test strict enforcement logic.
     */
    public function test_strict_enforcement_checks()
    {
        $user = Mockery::mock('WP_User');
        $user->roles = ['administrator'];
        $user->ID = 1;

        // Mock get_user_meta to return false (no bypass active)
        Functions\when('get_user_meta')->justReturn(false);

        // Run the filter
        $should_enforce = Enforcer::enforce_strict(false, $user);

        $this->assertTrue($should_enforce, 'Admin should be strictly enforced to use 2FA');
    }

    /**
     * Test the Emergency Bypass Logic.
     */
    public function test_bypass_logic_honors_time_limit()
    {
        $user = Mockery::mock('WP_User');
        $user->roles = ['administrator'];
        $user->ID = 1;

        // Scenario 1: Bypass is set and valid (future timestamp)
        Functions\when('get_user_meta')
            ->with(1, '_enterprise_2fa_bypass_expires', true)
            ->justReturn(time() + 300); // Expires in 5 mins

        $should_enforce = Enforcer::enforce_strict(true, $user);
        $this->assertFalse($should_enforce, 'Enforcement should be skipped if bypass is active');

        // Scenario 2: Bypass is expired (past timestamp)
        Functions\when('get_user_meta')
            ->with(1, '_enterprise_2fa_bypass_expires', true)
            ->justReturn(time() - 300); // Expired 5 mins ago

        Functions\expect('delete_user_meta')->once(); // Should clean up

        $should_enforce_expired = Enforcer::enforce_strict(true, $user);
        $this->assertTrue($should_enforce_expired, 'Enforcement should resume if bypass is expired');
    }
}
