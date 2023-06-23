<?php

namespace Sunnysideup\CMSDarkTheme\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\ORM\DataExtension;

class DarkThemePreference extends DataExtension
{
    private static $db = [
        'DarkModeSetting' => 'Enum("Not set, Dark, Light", "Not set")',
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
                    ->setDescription('Using a dark mode reduces your electricity use.')
            ]
        );
    }
}
