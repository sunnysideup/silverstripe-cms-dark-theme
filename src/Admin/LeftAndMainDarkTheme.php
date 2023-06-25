<?php

namespace Sunnysideup\CMSDarkTheme\Admin;

use SilverStripe\Admin\AdminRootController;
use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Control\Controller;
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

    // Maintain a lower priority than other administration sections
    // so that Director does not think they are actions of CMSMain
    private static $url_priority = 999;

    private static $menu_title = 'Select Display Mode';

    private static $menu_icon_class = 'font-icon-monitor';

    private static $required_permission_codes = false;

    private static $menu_priority = -999;

    private static $ignore_menuitem = false;

    private static $allowed_actions = [
        'setlightmode',
        'setdarkmode',
    ];

    private static $casting = [
        'FrontEndCode' => 'HTMLFragment',
    ];

    protected function init()
    {
        // set reading lang
        parent::init();

        // Requirements::javascript('silverstripe/cms: client/dist/js/SilverStripeNavigator.js');
        // Requirements::css('silverstripe/cms: client/dist/styles/bundle.css');


    }

    public function index($request)
    {
        if (self::is_dark_mode($this)) {
            self::set_mode('Light', $this);
        } else {
            self::set_mode('Dark', $this);
        }
        return $this->redirect('/admin/myprofile#Root_Display');
    }



    /**
     *
     * @param string|null $action Action to link to.
     * @return string
     */
    public function Link($action = null)
    {
        Requirements::customCSS(self::generate_css($this), self::CUSTOM_CODE);
        Requirements::customScript(self::generate_js($this), self::CUSTOM_CODE);
        if(!$action) {
            if(self::is_dark_mode($this)) {
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
        if(self::get_is_dark_mode_set_in_database($controller) === false) {
            $css .= '#Menu-Sunnysideup-CMSDarkTheme-Admin-LeftAndMainDarkTheme {display: none;}';
            $css .= '@media (prefers-color-scheme: dark) {'.$makeDarkCss.'}';
        } elseif(self::is_dark_mode($controller)) {
            $css .= $makeDarkCss;
        };

        return $css;
    }

    public static function menu_title($class = null, $localise = true): string
    {
        self::generate_js();
        return  self::is_dark_mode() ? 'Use Light Mode' : 'Use Dark Mode';
    }

    protected static function generate_js($controller = null): string
    {
        // set vars!
        $isDarkMode = self::is_dark_mode($controller);
        $js = '';
        if(! self::get_is_dark_mode_set_in_database($controller)) {
            $modifier =  $isDarkMode ? 'setlightmode' : 'setdarkmode';
            $innerText = $isDarkMode ? 'Use Light Mode' : 'Use Dark Mode';
            $js .= <<<js
            const el = document.getElementById("Menu-Sunnysideup-CMSDarkTheme-Admin-LeftAndMainDarkTheme");

            // Set the new href value to the element
            const a = el.querySelector('a')
            a.setAttribute("href", a.getAttribute("href") + "$modifier");
            a.querySelector('span.text').innerText = "$innerText";

js;
        }
        return $js;
    }



    /**
     *
     */
    public function setlightmode($request)
    {
        self::set_mode('Light', $this);
        return $this->redirectBack();
    }

    /**
     *
     * @return DBHTMLText HTML response with the rendered treeview
     */
    public function setdarkmode()
    {
        self::set_mode('Dark', $this);
        return $this->redirectBack();
    }

    protected static function is_dark_mode($controller = null): bool
    {
        return self::get_mode($controller) === 'Dark' ? true : false;
    }

    protected static function set_mode(string $mode, $controller = null)
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
        }
    }

    protected static $isDarkModeCache = null;
    protected static $isDarkModeCacheSetInDatabase = false;

    protected static function get_mode($controller = null): string
    {
        if(self::$isDarkModeCache === null) {
            $member = Security::getCurrentUser();
            if(self::is_valid_setting((string) $member->DarkModeSetting)) {
                self::$isDarkModeCacheSetInDatabase = true;
                if(! self::$isDarkModeCache) {
                    self::$isDarkModeCache = (string) $member->DarkModeSetting;
                }
            }
            $config = SiteConfig::current_site_config();
            if(self::is_valid_setting((string) $config->DarkModeSetting)) {
                if(! self::$isDarkModeCache) {
                    self::$isDarkModeCache = (string) $config->DarkModeSetting;
                }
            }
            if(! self::is_valid_setting((string) self::$isDarkModeCache)) {
                self::$isDarkModeCache = '';
            }
        }
        return (string) self::$isDarkModeCache;
    }
    protected static function get_is_dark_mode_set_in_database($controller = null): bool
    {
        // make sure we have checked it all out!
        self::get_mode($controller);
        return self::$isDarkModeCacheSetInDatabase;
    }

    protected static function is_valid_setting(string $mode): bool
    {
        return in_array($mode, ['Dark', 'Light']);
    }


}
