<?php

namespace JonoM\Helpers\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Core\Extension;

/**
 * Show creation and edit date in CMS
 */
class CreatedEditedExtension extends Extension
{
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab('Root.Main', ReadonlyField::create('ROLastEdited', ' Last edited', $this->getOwner()->obj('LastEdited')->Nice()));
        $fields->addFieldToTab('Root.Main', ReadonlyField::create('ROCreated', 'Created', $this->getOwner()->obj('Created')->Nice()));
    }
}
