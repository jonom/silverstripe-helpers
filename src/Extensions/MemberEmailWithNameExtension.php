<?php

namespace JonoM\Helpers\Extensions;

use SilverStripe\Core\Extension;

class MemberEmailWithNameExtension extends Extension
{
    private static $db = [];

    private static $has_one = [];

    private static $has_many = [];

    /**
     * Get Email address with name e.g. 'Jonathon Menz <jonomenz2@gmail.com>'.
     * Fallback to email address only.
     *
     * @return string
     */
    public function EmailWithName()
    {
        $name = $this->owner->getName();
        $email = $this->owner->Email;

        return ($name) ? "$name <$email>" : $email;
    }
}
