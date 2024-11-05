<?php

namespace JonoM\Helpers\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataExtension;

/**
 * Show creation and edit date in CMS
 */
class CreatedEditedExtension extends DataExtension
{
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab('Root.Main', ReadonlyField::create('ROLastEdited', ' Last edited', $this->owner->obj('LastEdited')->Nice()));
        $fields->addFieldToTab('Root.Main', ReadonlyField::create('ROCreated', 'Created', $this->owner->obj('Created')->Nice()));
    }
}
