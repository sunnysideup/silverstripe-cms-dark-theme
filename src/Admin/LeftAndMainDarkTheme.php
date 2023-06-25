<?php

namespace Sunnysideup\CMSDarkTheme\Admin;

use SilverStripe\Admin\AdminRootController;
use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Config\Config;
use SilverStripe\Security\Security;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\Requirements;

class LeftAndMainDarkTheme extends LeftAndMain
{
    public const CUSTOM_CODE = 'DarkModeCustomCssAndJs';

    private static $url_segment = 'darkmode';

    private static $make_dark_css = '
        html {
            filter: invert(1) contrast(0.95) saturate(0.5) hue-rotate(180deg);
        }
        img,
        .gallery-item__thumbnail,
        iframe[name="cms-preview-iframe"] {
            filter: invert(1) contrast(calc(1/0.95)) saturate(calc(1/0.5)) hue-rotate(-180deg);
        }
    ';

    private static $url_rule = '/$Action/$ID/$OtherID';

    private static $url_priority = 999;

    private static $menu_title = 'Select Display Mode';

    private static $menu_icon_class = 'font-icon-monitor';

    private static $required_permission_codes = false;

    private static $menu_priority = -999;

    // private static $ignore_menuitem = false;

    private static $allowed_actions = [
        'setlightmode',
        'setdarkmode',
    ];

    protected function init()
    {
        parent::init();

        // Requirements::javascript('silverstripe/cms: client/dist/js/SilverStripeNavigator.js');
        // Requirements::css('silverstripe/cms: client/dist/styles/bundle.css');


    }

    /**
     * set mode and then
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function index($request)
    {
        if (self::is_dark_mode()) {
            self::set_mode('Light', $this);
        } else {
            self::set_mode('Dark', $this);
        }
        return $this->redirect('/admin/myprofile#Root_Cms');
    }


    /**
     * HACK ALERT - we add the CSS / JS here as this seems the easiest place to add it
     * even though it does not make any sense
     *
     * @param string|null $action Action to link to.
     * @return string
     */
    public function Link($action = null)
    {
        Requirements::customCSS(self::generate_css($this), self::CUSTOM_CODE);
        Requirements::customScript(self::generate_js($this), self::CUSTOM_CODE);
        if(!$action) {
            if(self::is_dark_mode()) {
                $action = 'setlightmode';
            } else {
                $action = 'setdarkmode';
            }
        }
        return parent::Link($action);
    }

    protected static function generate_css($controller = null): string
    {
        $makeDarkCss = Config::inst()->get(static::class, 'make_dark_css');
        // use browser setting.
        $css = '';
        if(self::get_hide_menu_item()) {
            $css .= '#Menu-Sunnysideup-CMSDarkTheme-Admin-LeftAndMainDarkTheme {display: none;}';
        }
        if(self::get_is_dark_mode_set_in_database() === false) {
            $css .= '@media (prefers-color-scheme: dark) {'.$makeDarkCss.'}';
        } elseif(self::is_dark_mode()) {
            $css .= $makeDarkCss;
        };

        return $css;
    }

    public static function menu_title($class = null, $localise = true): string
    {
        return  self::is_dark_mode() ? '🌖 Use Light Mode' : '🌘 Use Dark Mode';
    }

    protected static function generate_js($controller = null): string
    {
        // set vars!
        $isDarkMode = self::is_dark_mode();
        $js = '';
        $modifier =  $isDarkMode ? 'setlightmode' : 'setdarkmode';
        $innerText = self::menu_title();
        $js .= <<<js
        const el = document.querySelector(".cms-help__links a[href='/admin/darkmode/']");

        // Set the new href value to the element
        // el.setAttribute("href", el.getAttribute("href") + "$modifier");
        el.innerText = "$innerText";
        el.target = "self";

js;
        return $js;
    }

    public function setlightmode($request): HTTPResponse
    {
        self::set_mode('Light', $this);
        return $this->redirectBack();
    }

    public function setdarkmode($request): HTTPResponse
    {
        self::set_mode('Dark', $this);
        return $this->redirectBack();
    }

    protected static function is_dark_mode(): bool
    {
        return self::get_mode() === 'Dark' ? true : false;
    }

    protected static function set_mode(string $mode, $controller = null): void
    {
        if(! self::is_valid_setting((string) $mode)) {
            user_error('Setting must be Dark or Light');
        }
        if(! $controller) {
            $controller = Controller::curr();
        }
        $member = Security::getCurrentUser();
        if($member) {
            $member->DarkModeSetting = $mode;
            $member->write();
        } else {
            user_error('Could not set mode.');
        }
    }

    protected static $darkModeValueCache = null;

    /**
     * has any value been set in the database?
     *
     * @var boolean
     */
    protected static $darkModeSetInDatabaseCache = false;

    protected static function get_mode(): string
    {
        if(self::$darkModeValueCache === null) {
            $member = Security::getCurrentUser();
            if(self::is_valid_setting((string) $member->DarkModeSetting)) {
                self::$darkModeSetInDatabaseCache = true;
                self::$darkModeValueCache = (string) $member->DarkModeSetting;
            }
            $config = SiteConfig::current_site_config();
            if(self::is_valid_setting((string) $config->DarkModeSetting)) {
                self::$darkModeSetInDatabaseCache = true;
                if(! self::$darkModeValueCache) {
                    self::$darkModeValueCache = (string) $config->DarkModeSetting;
                }
            }
            if(! self::is_valid_setting((string) self::$darkModeValueCache)) {
                self::$darkModeValueCache = '';
            }
        }
        return (string) self::$darkModeValueCache;
    }

    /**
     * has any relevant value been set in the database?
     *
     * @var boolean
     */
    protected static function get_is_dark_mode_set_in_database(): bool
    {
        return self::$darkModeSetInDatabaseCache;
    }

    protected static function get_hide_menu_item(): bool
    {
        $member = Security::getCurrentUser();
        if($member->HideDarkModeSettingOptionFromMenu) {
            return true;
        }
        $config = SiteConfig::current_site_config();
        if($config->HideDarkModeSettingOptionFromMenu) {
            return true;
        }
        return false;
    }

    protected static function is_valid_setting(string $mode): bool
    {
        return in_array($mode, ['Dark', 'Light']);
    }


}
