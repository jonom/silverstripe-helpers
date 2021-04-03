<?php

namespace JonoM\Helpers\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;

class SortableExtension extends DataExtension
{
    private static $db = [
        'Sort' => 'Int',
    ];

    private static $default_sort = 'Sort ASC';

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('Sort');
    }

    public function onBeforeWrite()
    {
        // Add new items to the end of the stack
        $class = get_class($this->owner);
        if (!$this->owner->Sort) {
            $this->owner->Sort = $class::get()->max('Sort') + 1;
        }
    }
}
