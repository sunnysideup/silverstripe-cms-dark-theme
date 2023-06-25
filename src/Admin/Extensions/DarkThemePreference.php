<?php

namespace Sunnysideup\CMSDarkTheme\Extensions;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\View\Requirements;

class DarkThemePreference extends DataExtension
{
    private static $db = [
        'DarkModeSetting' => 'Enum("Use browser setting, Dark, Light", "Use browser setting")',
        'HideDarkModeSettingOptionFromMenu' => 'Boolean',
    ];

    private static $field_labels = [
        'DarkModeSetting' => 'Preferred Display Mode for CMS',
    ];
    public function updateCMSFields(FieldList $fields)
    {
        $fieldLabels = $this->getOwner()->fieldLabels();
        $fields->addFieldsToTab(
            'Root.Display',
            [
                OptionsetField::create(
                    'DarkModeSetting',
                    $fieldLabels['DarkModeSetting'] ?? self::$field_labels['DarkModeSetting'],
                    $this->getOwner()->dbObject('DarkModeSetting')->enumValues()
                )
                    ->setDescription('Using a dark mode may reduce your electricity use. Please reload browser window to see change.'),
                CheckboxField::create('HideDarkModeSettingOptionFromMenu', 'Hide option from menu')
                    ->setDescription('By ticking this box, you no longer have the option to change it from the left-hand-side menu in the CMS.')
            ]
        );
    }



}
