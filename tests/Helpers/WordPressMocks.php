<?php
/**
 * WordPress mocking utilities
 *
 * @package Enterprise\Security\TwoFactor\Tests
 */

namespace Enterprise\Security\TwoFactor\Tests\Helpers;

use Brain\Monkey\Functions;

/**
 * Utility class for mocking WordPress functions and objects
 */
class WordPressMocks
{
    /**
     * Mock a WordPress user object
     *
     * @param int    $id User ID
     * @param string $login User login
     * @param string $email User email
     * @param array  $roles User roles
     * @return \stdClass Mock user object
     */
    public static function mockUser(int $id = 1, string $login = 'testuser', string $email = 'test@example.com', array $roles = ['subscriber']): \stdClass
    {
        $user = new \stdClass();
        $user->ID = $id;
        $user->user_login = $login;
        $user->user_email = $email;
        $user->roles = $roles;
        $user->data = new \stdClass();
        $user->data->ID = $id;
        $user->data->user_login = $login;
        $user->data->user_email = $email;

        return $user;
    }

    /**
     * Mock get_current_user_id function
     *
     * @param int $user_id User ID to return
     * @return void
     */
    public static function mockGetCurrentUserId(int $user_id = 1): void
    {
        Functions\when('get_current_user_id')->justReturn($user_id);
    }

    /**
     * Mock get_user_by function
     *
     * @param \stdClass|false $user User object to return or false
     * @return void
     */
    public static function mockGetUserBy($user = null): void
    {
        if ($user === null) {
            $user = self::mockUser();
        }
        Functions\when('get_user_by')->justReturn($user);
    }

    /**
     * Mock get_userdata function
     *
     * @param \stdClass|false $user User object to return or false
     * @return void
     */
    public static function mockGetUserdata($user = null): void
    {
        if ($user === null) {
            $user = self::mockUser();
        }
        Functions\when('get_userdata')->justReturn($user);
    }

    /**
     * Mock is_multisite function
     *
     * @param bool $is_multisite Whether this is a multisite installation
     * @return void
     */
    public static function mockIsMultisite(bool $is_multisite = false): void
    {
        Functions\when('is_multisite')->justReturn($is_multisite);
    }

    /**
     * Mock get_blog_option function for multisite
     *
     * @param mixed $value Value to return
     * @return void
     */
    public static function mockGetBlogOption($value): void
    {
        Functions\when('get_blog_option')->justReturn($value);
    }

    /**
     * Mock get_option function
     *
     * @param string $option_name Option name
     * @param mixed  $value Value to return
     * @return void
     */
    public static function mockGetOption(string $option_name, $value): void
    {
        Functions\when('get_option')->with($option_name)->justReturn($value);
    }

    /**
     * Mock update_option function
     *
     * @return void
     */
    public static function mockUpdateOption(): void
    {
        Functions\when('update_option')->justReturn(true);
    }

    /**
     * Mock delete_option function
     *
     * @return void
     */
    public static function mockDeleteOption(): void
    {
        Functions\when('delete_option')->justReturn(true);
    }

    /**
     * Mock current_user_can function
     *
     * @param bool $can Whether the user has the capability
     * @return void
     */
    public static function mockCurrentUserCan(bool $can = true): void
    {
        Functions\when('current_user_can')->justReturn($can);
    }

    /**
     * Mock user_can function
     *
     * @param bool $can Whether the user has the capability
     * @return void
     */
    public static function mockUserCan(bool $can = true): void
    {
        Functions\when('user_can')->justReturn($can);
    }

    /**
     * Mock get_user_meta function
     *
     * @param mixed $value Value to return
     * @return void
     */
    public static function mockGetUserMeta($value): void
    {
        Functions\when('get_user_meta')->justReturn($value);
    }

    /**
     * Mock update_user_meta function
     *
     * @return void
     */
    public static function mockUpdateUserMeta(): void
    {
        Functions\when('update_user_meta')->justReturn(true);
    }

    /**
     * Mock delete_user_meta function
     *
     * @return void
     */
    public static function mockDeleteUserMeta(): void
    {
        Functions\when('delete_user_meta')->justReturn(true);
    }

    /**
     * Mock wp_get_current_user function
     *
     * @param \stdClass|null $user User object to return
     * @return void
     */
    public static function mockWpGetCurrentUser($user = null): void
    {
        if ($user === null) {
            $user = self::mockUser();
        }
        Functions\when('wp_get_current_user')->justReturn($user);
    }

    /**
     * Mock is_user_logged_in function
     *
     * @param bool $is_logged_in Whether user is logged in
     * @return void
     */
    public static function mockIsUserLoggedIn(bool $is_logged_in = true): void
    {
        Functions\when('is_user_logged_in')->justReturn($is_logged_in);
    }

    /**
     * Mock class_exists for checking if a plugin is active
     *
     * @param string $class_name Class name to check
     * @param bool   $exists Whether the class exists
     * @return void
     */
    public static function mockClassExists(string $class_name, bool $exists = true): void
    {
        Functions\when('class_exists')->with($class_name)->justReturn($exists);
    }
}
