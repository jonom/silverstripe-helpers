<?php

namespace JonoM\Helpers\Fields;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\ORM\SS_List;

class ImageField extends UploadField
{
    public function __construct($name, $title = null, SS_List $items = null)
    {
        parent::__construct($name, $title);
        $this->setAllowedFileCategories('image');
        $this->setAllowedMaxFileNumber(1);
    }
}
