<?php

namespace JonoM\Helpers\Utility;

use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBText;

class Cast
{
    /**
     * Cast a raw string to DBText for use in templates.
     * Accepts arrays as well.
     *
     * @param string|array $raw
     * @return DBText
     */
    public static function Text($raw)
    {
        return self::cast($raw, DBText::class);
    }

    /**
     * Cast a raw string of html to DBHTMLText for use in templates.
     * Accepts arrays as well.
     *
     * @param string|array $raw
     * @return DBHTMLText
     */
    public static function HTML($raw)
    {
        return self::cast($raw, DBHTMLText::class);
    }

    protected static function cast($stringOrArray, $type)
    {
        $array = is_array($stringOrArray);
        if (!$array) $stringOrArray = [$stringOrArray];
        foreach ($stringOrArray as &$raw) {
            $raw = DBField::create_field($type, $raw);
        }
        return $array ? $stringOrArray : $stringOrArray[0];
    }
}
