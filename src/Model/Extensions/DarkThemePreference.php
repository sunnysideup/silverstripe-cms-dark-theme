<?php

namespace Sunnysideup\CMSDarkTheme\Model\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Core\Extension;

/**
 * Class \Sunnysideup\CMSDarkTheme\Model\Extensions\DarkThemePreference.
 *
 * @property SiteConfig|Member|DarkThemePreference $owner
 * @property ?string $DarkModeSetting
 */
class DarkThemePreference extends Extension
{
    private static $db = [
        'DarkModeSetting' => 'Enum("Use browser setting, Dark, Light", "Use browser setting")',
    ];

    private static $dark_theme_also_move_locale = true;

    private static $field_labels = [
        'DarkModeSetting' => 'Preferred Display Mode for CMS',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fieldLabels = $this->getOwner()->fieldLabels();
        $owner = $this->getOwner();
        if ($owner->Config()->get('dark_theme_also_move_locale')) {
            $localeField = $fields->dataFieldByName('Locale');
            if ($localeField) {
                $fields->addFieldsToTab(
                    'Root.Preferences',
                    [
                        $localeField
                    ]
                );
            }
        }
        $description = _t(
            'Sunnysideup\CMSDarkTheme\Model\Extensions\DarkThemePreference.DarkModeSettingDescription',
            'Using a dark mode may reduce your electricity use. Please reload browser window to update your mode.'
        );
        $fields->addFieldsToTab(
            'Root.Preferences',
            [
                OptionsetField::create(
                    'DarkModeSetting',
                    $fieldLabels['DarkModeSetting'] ?? self::$field_labels['DarkModeSetting'],
                    $this->getOwner()->dbObject('DarkModeSetting')->enumValues()
                )
                    ->setDescription($description),
            ]
        );
        $preferencesName = _t('Sunnysideup\CMSDarkTheme\Model\Extensions\DarkThemePreference.PreferencesTabName', 'Preferences');
        $fields->findTab('Root.Preferences')->setTitle($preferencesName);
    }
}
