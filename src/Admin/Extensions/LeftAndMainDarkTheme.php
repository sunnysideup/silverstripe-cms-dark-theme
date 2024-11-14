<?php

namespace Sunnysideup\CMSDarkTheme\Admin\Extensions;

use SilverStripe\Admin\LeftAndMain;
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

    // contrast(calc(1/0.95)) saturate(calc(1/0.5))
    private static $make_dark_css = '

/* Apply dark theme to entire HTML */
html {
    filter: invert(0.9) hue-rotate(180deg);
}

/* Keep images and specific elements unchanged */
img,
.gallery-item__thumbnail,
iframe[name="cms-preview-iframe"],
.btn-primary,
.cms-content-loading-spinner,
.cms-help__links,
.cms-help__logo,
.cms-menu__header,
.ss-loading-screen__text,
.uploadfield-item__thumbnail {
    filter: invert(1) hue-rotate(-180deg);
}
    ';

    public function updateClientConfig(array $clientConfig)
    {
        Requirements::customCSS(self::generate_display_mode_css(), self::CUSTOM_CODE);
        Requirements::customScript(self::generate_display_mode_js(), self::CUSTOM_CODE . 'JS');
    }

    public static function get_display_mode_menu_title($class = null, $localise = true): string
    {
        return LeftAndMainDarkThemeApi::is_dark_mode() ? '🌖 Use Light Mode' : '🌘 Use Dark Mode';
        // '🌗 Use Browser Setting'
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
        $undetermined = 'false';
        if (false === LeftAndMainDarkThemeApi::get_is_dark_mode_set_in_database()) {
            $undetermined = 'true';
        }

        return $js . <<<js
        document.addEventListener("DOMContentLoaded", function() {
            // Check if the user prefers dark mode
            const el = document.querySelector(".cms-help__links a[href='/admin/displaymode/']");
            let hrefValue = ''
            if(el) {
                const el = document.querySelector(".cms-help__links a[href='/admin/displaymode/']");
                hrefValue = el.getAttribute("href") + "{$modifier}"
                // Set the new href value to the element
                el.setAttribute("href", hrefValue);
                el.innerText = "{$innerText}";
                el.target = "_parent";
            }
            if ({$undetermined} && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {

                // Replace 'dark' with 'light' in the href attribute
                hrefValue = el.getAttribute("href").replace('dark', 'light');
                el.setAttribute("href", hrefValue);

                // Update inner text
                el.innerText = "🌖 use light mode";
                el.target = "_parent";
            }
            el.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent default link behavior

                // Fetch the URL without redirecting
                fetch(hrefValue).then(() => {
                    // Reload the page after the fetch completes
                    window.location.reload();
                });
            });
        });

js;
    }
}
