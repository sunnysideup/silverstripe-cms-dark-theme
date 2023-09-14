<?php

namespace Sunnysideup\CMSDarkTheme\Admin\Extensions;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;
use Sunnysideup\CMSDarkTheme\Api\LeftAndMainDarkThemeApi;

// to do - turn into extension.

/**
 * Class \Sunnysideup\CMSDarkTheme\Admin\Extensions\LeftAndMainDarkTheme.
 *
 * @property LeftAndMain|LeftAndMainDarkTheme $owner
 */
class LeftAndMainDarkTheme extends Extension
{
    public const CUSTOM_CODE = 'DarkModeCustomCssAndJs';

    private static $make_dark_css = '
        html {
            filter: invert(1) contrast(0.95) saturate(0.5) hue-rotate(180deg);
        }
        img,
        .gallery-item__thumbnail,
        iframe[name="cms-preview-iframe"],
        .uploadfield-item__thumbnail {
            filter: invert(1) contrast(calc(1/0.95)) saturate(calc(1/0.5)) hue-rotate(-180deg);
        }
    ';

    public function updateClientConfig(array $clientConfig)
    {
        Requirements::customCSS(self::generate_display_mode_css($this), self::CUSTOM_CODE);
        Requirements::customScript(self::generate_display_mode_js($this), self::CUSTOM_CODE);
    }

    public static function get_display_mode_menu_title($class = null, $localise = true): string
    {
        return  LeftAndMainDarkThemeApi::is_dark_mode() ? '🌖 Use Light Mode' : '🌘 Use Dark Mode';
    }

    protected static function generate_display_mode_css(): string
    {
        $makeDarkCss = Config::inst()->get(static::class, 'make_dark_css');
        // use browser setting.
        $css = '';
        if (false === LeftAndMainDarkThemeApi::get_is_dark_mode_set_in_database()) {
            $css .= '@media (prefers-color-scheme: dark) {' . $makeDarkCss . '}';
        } elseif (LeftAndMainDarkThemeApi::is_dark_mode()) {
            $css .= $makeDarkCss;
        }

        return $css;
    }

    protected static function generate_display_mode_js(): string
    {
        $isDarkMode = LeftAndMainDarkThemeApi::is_dark_mode();
        $js = '';
        $modifier = $isDarkMode ? 'setlightmode' : 'setdarkmode';
        $innerText = LeftAndMainDarkThemeApi::get_display_mode_menu_title();
        $js .= <<<js
        const el = document.querySelector(".cms-help__links a[href='/admin/displaymode/']");

        // Set the new href value to the element
        // el.setAttribute("href", el.getAttribute("href") + "{$modifier}");
        el.innerText = "{$innerText}";
        el.target = "_parent";

js;

        return $js;
    }
}
