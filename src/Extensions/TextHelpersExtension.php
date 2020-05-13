<?php

namespace JonoM\Helpers\Extensions;

use JonoM\Helpers\Utility\Helpers;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;

class TextHelpersExtension extends Extension
{

    /**
     * Convert raw text to html and replace single line breaks with <br> and double line breaks with paragraphs.
     *
     * @return string
     */
    public function Raw2P()
    {
        return DBField::create_field(DBHTMLText::class, Helpers::raw2p($this->owner->value));
    }
    /**
     * Convert raw text to list items. Output needs to be wrapped in a ul or ol element.
     *
     * @return string
     */
    public function Raw2Li()
    {
        return DBField::create_field(DBHTMLText::class, Helpers::raw2li($this->owner->value));
    }
    /**
     * Get an array list from raw text where each new line becomes a new array item
     *
     * @return string
     */
    public function Lines2List()
    {
        return ArrayList::create(Helpers::lines2array($this->owner->value));
    }

    public function AddProtocol()
    {
        return Helpers::addProtocolToURL($this->owner->value);
    }

    public function RemoveProtocol()
    {
        return Helpers::removeProtocolFromURL($this->owner->value);
    }

    public function NeatLink($charLimit = 25, $newWindow = true)
    {
        return Helpers::neatLink($this->owner->value, $charLimit, $newWindow);
    }

    /**
     * Truncate a string, but only if it will remove a reasonable number of characters
     *
     * @access public
     * @param int $maxlength (default: 25)
     * @param int $threshold (default: 5)
     * @return String
     */
    public function Truncate($maxlength = 25, $threshold = 5)
    {
        return Helpers::truncateString($this->owner->value, $maxlength, $threshold);
    }

    public function Trim()
    {
        return DBField::create_field(get_class($this->owner), trim($this->owner->value));
    }
}
