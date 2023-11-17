<?php

namespace JonoM\Helpers\Utility;

use SilverStripe\Core\Manifest\ModuleManifest;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;

class Helpers
{
    /**
     * Get the name of the the project folder, as registered in project yml file
     *
     * @return string
     */
    public static function project()
    {
        return ModuleManifest::config()->get('project') ?: 'app';
    }

    public static function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === '' || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    public static function endsWith($haystack, $needle)
    {
        // search forward starting from end minus needle length characters
        return $needle === '' || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }

    public static function removeProtocolFromURL($url)
    {
        // Remove protocol from URL, and trailing slash
        $urlParts = explode('://', "$url");

        return trim(end($urlParts), '/');
    }

    public static function addProtocolToURL($url)
    {
        // Add http:// protocol if not included
        if (strpos($url, '://') === false) {
            return 'http://' . $url;
        }

        return $url;
    }

    /**
     * Generate markup for a neat looking shortened URL
     * @param string $url
     * @param int $charLimit
     * @param bool $newWindow
     * @return DBHTMLText
     */
    public static function neatLink($url, $charLimit = 25, $newWindow = true)
    {
        // Create a link of max length without protocol
        if (!$url) {
            return false;
        }
        $label = self::neatLinkLabel($url, $charLimit);
        $link = self::addProtocolToURL($url);
        $target = $newWindow ? ' target="_blank"' : '';

        return DBField::create_field(DBHTMLText::class, "<a href=\"$link\"$target>$label</a>");
    }

    /**
     * Get a neat looking representation of a URL
     * @param string $url
     * @param int $charLimit
     */
    public static function neatLinkLabel($url, $charLimit = 25)
    {
        if (!$url) {
            return false;
        }
        // Remove protocol
        $label = self::removeProtocolFromURL($url);
        // Remove trailing slash
        $label = trim("$label", '/');
        // Lowercase
        $label = strtolower($label);
        // Truncate
        return self::truncateString($label, $charLimit);
    }

    public static function truncateString($longString, $maxlength = 25, $threshold = 5)
    {
        // Truncate a string with ellipsis in the middle
        // From https://css-tricks.com/snippets/php/truncate-string-in-center/
        $separator = '...';
        $separatorlength = strlen($separator);
        $maxlength = $maxlength - $separatorlength;
        $start = $maxlength / 2;
        $trunc = strlen($longString) - $maxlength;
        // Don't truncate for just a few characters
        return ($trunc > $threshold)
            ? substr_replace($longString, $separator, $start, $trunc)
            : $longString;
    }

    public static function cmsMessageBox($inlineHTML, $class = 'notice')
    {
        // Class can be notice | warning| error | good
        return LiteralField::create('', "<div class=\"message $class\"><p>$inlineHTML</p></div>");
    }

    /**
     * Convert raw text to paragraphs.
     * Preserve white space by replacing single line breaks with <br> and double line breaks with paragraphs.
     *
     * Before:
     * -------
     * Hey this is a paragraph.
     *
     * And this is another one.
     * With a manual line break.
     * -------
     *
     * After:
     * -------
     * <p>Hey this is a paragraph.</p>
     * <p>And this is another one.<br>
     * With a manual line break.</p>
     * -------
     *
     * @param string $text Text to convert
     *
     * @return string
     */
    public static function raw2p($text)
    {
        $xml = htmlspecialchars(trim("$text"), ENT_QUOTES, 'UTF-8');
        $xml = nl2br($xml);
        return $xml ? '<p>' . preg_replace('#(<br \/>\R+){2}#', "</p>\r\n<p>", $xml) . '</p>' : false;
    }

    /**
     * Convert raw text to list items. Output needs to be wrapped in a ul or ol element.
     *
     * Before:
     * -------
     * Hey this is a paragraph.
     *
     * And this is another one.
     * With a manual line break.
     * -------
     *
     * After:
     * -------
     * <li>Hey this is a paragraph.</li>
     * <li>And this is another one.</li>
     * <li>With a manual line break.</li>
     * -------
     *
     * @param string $text Text to convert
     *
     * @return string
     */
    public static function raw2li($text)
    {
        // Remove empty lines
        $xml = htmlspecialchars(trim("$text"), ENT_QUOTES, 'UTF-8');
        return $xml ? '<li>' . preg_replace('(\R+)', "</li>\r\n<li>", $xml) . '</li>' : false;
    }

    /**
     * Convert each line in raw text to a span tag.
     *
     * Before:
     * -------
     * I am a line
     * I am another line
     * -------
     *
     * After:
     * -------
     * <span class="myClass">I am a line</span> <span class="myClass">I am another line</span>
     * -------
     *
     * @param string $text Text to convert
     *
     * @return string
     */
    public static function raw2span($text, $class)
    {
        // Remove empty lines
        $xml = htmlspecialchars(trim("$text"), ENT_QUOTES, 'UTF-8');
        return $xml ? "<span class=\"$class\">" . preg_replace('(\R+)', "</span>\r\n<span class=\"$class\">", $xml) . '</span>' : false;
    }

    /**
     * Remove empty lines including those which only contain whitespace
     * and trim white space from the start and end of each line
     *
     * @param string $text
     * @return string
     */
    public static function trimLines($text)
    {
        return preg_replace('/(\h*\R+\h*)+/', PHP_EOL, trim("$text"));
    }

    /**
     * Convert line breaks to a full stop + space
     *
     * @param string $text
     * @return string
     */
    public static function toOneLine($text)
    {
        // Remove blank lines
        $text = self::trimLines($text);
        // Leave end of line punctuation intact
        $text = preg_replace('/([!?.,;:])\R/', '$1 ', $text);
        // Add punctuation if missing
        return preg_replace('/\R/', '. ', $text);
    }

    /**
     * Create an array from raw text with a new item for each line. Empty lines will be removed.
     *
     * @param string $text
     * @return array
     */
    public static function lines2array($text)
    {
        return array_filter(explode(PHP_EOL, self::trimLines($text)));
    }
}
