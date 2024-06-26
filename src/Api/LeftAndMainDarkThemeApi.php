<?php

namespace Sunnysideup\CMSDarkTheme\Api;

use SilverStripe\Security\Security;
use SilverStripe\SiteConfig\SiteConfig;

class LeftAndMainDarkThemeApi
{
    /**
     * Dar or Light or null.
     *
     * @var null|string
     */
    protected static $darkModeValueCache;

    /**
     * has any value been set in the database?
     *
     * @var bool
     */
    protected static $darkModeSetInDatabaseCache = false;

    public static function get_display_mode_menu_title(): string
    {
        return self::is_dark_mode() ? '🌖 Use Light Mode' : '🌘 Use Dark Mode';
    }

    public static function is_dark_mode(): bool
    {
        return 'Dark' === self::get_display_mode();
    }

    public static function set_display_mode(string $mode): void
    {
        if (! self::is_valid_display_mode_setting($mode)) {
            user_error('Setting must be Dark or Light');
        }
        $member = Security::getCurrentUser();
        if ($member) {
            $member->DarkModeSetting = $mode;
            $member->write();
        } else {
            user_error('Could not set mode.');
        }
    }

    public static function get_display_mode(): string
    {
        if (null === self::$darkModeValueCache) {
            $member = Security::getCurrentUser();
            if (self::is_valid_display_mode_setting((string) $member->DarkModeSetting)) {
                self::$darkModeSetInDatabaseCache = true;
                self::$darkModeValueCache = (string) $member->DarkModeSetting;
            }
            $config = SiteConfig::current_site_config();
            if (self::is_valid_display_mode_setting((string) $config->DarkModeSetting)) {
                self::$darkModeSetInDatabaseCache = true;
                if (! self::$darkModeValueCache) {
                    self::$darkModeValueCache = (string) $config->DarkModeSetting;
                }
            }
            if (! self::is_valid_display_mode_setting((string) self::$darkModeValueCache)) {
                self::$darkModeValueCache = '';
            }
        }

        return (string) self::$darkModeValueCache;
    }

    /**
     * has any relevant value been set in the database?
     *
     * @var bool
     */
    public static function get_is_dark_mode_set_in_database(): bool
    {
        return self::$darkModeSetInDatabaseCache;
    }

    public static function is_valid_display_mode_setting(string $mode): bool
    {
        return in_array($mode, ['Dark', 'Light'], true);
    }
}
