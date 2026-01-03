<?php
/**
 * Base TestCase class for all tests
 *
 * @package StarisianTechnologies\Sparxstar2FA\Tests
 */

namespace StarisianTechnologies\Sparxstar2FA\Tests\Helpers;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * Base TestCase class that sets up Brain Monkey for WordPress function mocking
 */
abstract class TestCase extends PHPUnitTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Set up the test environment before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        // Common WordPress function mocks that most tests will need
        $this->setupCommonMocks();
    }

    /**
     * Clean up the test environment after each test
     */
    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Set up common WordPress function mocks
     */
    protected function setupCommonMocks(): void
    {
        // Mock common WordPress functions
        Monkey\Functions\when('__')->returnArg();
        Monkey\Functions\when('esc_html__')->returnArg();
        Monkey\Functions\when('esc_attr__')->returnArg();
        Monkey\Functions\when('esc_html_e')->returnArg();
        Monkey\Functions\when('esc_attr_e')->returnArg();
        Monkey\Functions\when('esc_html')->returnArg();
        Monkey\Functions\when('esc_attr')->returnArg();
        Monkey\Functions\when('esc_url')->returnArg();
        Monkey\Functions\when('wp_kses_post')->returnArg();
        Monkey\Functions\when('sanitize_text_field')->returnArg();
        Monkey\Functions\when('absint')->returnArg();
    }

    /**
     * Helper method to mock a WordPress filter
     *
     * @param string $hook The filter hook name
     * @param mixed  $value The value to return
     * @return void
     */
    protected function mockFilter(string $hook, $value): void
    {
        Monkey\Filters\expectApplied($hook)->andReturn($value);
    }

    /**
     * Helper method to mock a WordPress action
     *
     * @param string $hook The action hook name
     * @return void
     */
    protected function mockAction(string $hook): void
    {
        Monkey\Actions\expectDone($hook);
    }

    /**
     * Helper method to assert that a WordPress action was fired
     *
     * @param string $hook The action hook name
     * @return void
     */
    protected function assertActionFired(string $hook): void
    {
        $this->assertTrue(
            Monkey\Actions\has($hook),
            "Action '{$hook}' was not fired"
        );
    }

    /**
     * Helper method to assert that a WordPress filter was applied
     *
     * @param string $hook The filter hook name
     * @return void
     */
    protected function assertFilterApplied(string $hook): void
    {
        $this->assertTrue(
            Monkey\Filters\has($hook),
            "Filter '{$hook}' was not applied"
        );
    }
}
