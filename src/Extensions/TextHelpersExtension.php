<?php

namespace JonoM\Helpers\Extensions;

use JonoM\Helpers\Utility\Helpers;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBText;

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
    public function Raw2Span($class = "")
    {
        return DBField::create_field(DBHTMLText::class, Helpers::raw2span($this->owner->value, $class));
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
        return Helpers::truncateString($this->BetterPlain(), $maxlength, $threshold);
    }

    public function Trim()
    {
        return DBField::create_field(DBText::class, trim($this->BetterPlain()));
    }

    public function Slug()
    {
        return DBField::create_field(DBText::class, strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $this->BetterPlain())));
    }

    /**
     * Like Plain() but adds newlines for headings as well.
     */
    public function BetterPlain()
    {
        if ($this->owner->config()->get('escape_type') == 'xml') {
            $text = preg_replace('/\<br(\s*)?\/?\>/i', "\n", $this->owner->RAW());

            // Convert heading and paragraph breaks to multi-lines
            $text = preg_replace('/(\<\/[ph]\d?\>)/i', "$1\n\n", $text);

            // Strip out HTML tags
            $text = strip_tags($text);

            // Implode >3 consecutive linebreaks into 2
            $text = preg_replace('~(\R){2,}~u', "\n\n", $text);

            // Decode HTML entities back to plain text
            return trim(Convert::xml2raw($text));
        }
        return $this->owner;
    }

    public function ToOneLine()
    {
        $val = $this->owner->value;
        $plain = $this->BetterPlain();
        return DBField::create_field(DBText::class, Helpers::toOneLine($this->BetterPlain()));
    }
}
