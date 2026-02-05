<?php

namespace JonoM\Helpers\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Core\Extension;

class SortableExtension extends Extension
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
        $class = get_class($this->getOwner());
        if (!$this->getOwner()->Sort) {
            $this->getOwner()->Sort = $class::get()->max('Sort') + 1;
        }
    }
}
