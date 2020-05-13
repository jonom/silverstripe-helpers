<?php

namespace JonoM\Helpers\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\View\Parsers\URLSegmentFilter;

class UniqueURLSegmentExtension extends DataExtension
{
    private static $db = [
        'URLSegment' => 'Varchar(255)',
    ];

    public function onBeforeWrite()
    {
        // If there is no URLSegment set, generate one from Title
        if (!$this->owner->URLSegment) {
            $this->owner->URLSegment = $this->generateURLSegment($this->owner->getTitle());
        }

        // validate segment or create default
        if (!$this->owner->isInDB() || $this->owner->isChanged('URLSegment')) {
            $this->owner->URLSegment = $this->generateURLSegment($this->owner->URLSegment);
            $this->makeURLSegmentUnique();
        }
    }

    /**
     * Check if there is already a piece of content of this type with this URLSegment.
     * Override this method for more nuanced logic.
     * @param string $URLSegment
     */
    public function URLSegmentInUse($URLSegment)
    {
        $class = $this->ownerBaseClass;
        $items = $class::get()->filter('URLSegment', $URLSegment);
        // Exclude this item if already written
        if ($this->owner->ID > 0) {
            $items = $items->exclude('ID', $this->owner->ID);
        }

        return $items->exists();
    }

    /**
     * Generate a URL segment based on the title provided.
     *
     * @param string $title
     *
     * @return string Generated url segment
     */
    public function generateURLSegment($title)
    {
        $filter = URLSegmentFilter::create();
        $t = $filter->filter($title);

        // Fallback to generic page name if path is empty (= no valid, convertable characters)
        if (!$t || $t == '-' || $t == '-1') {
            $class = strtolower($this->owner->ClassName);
            $t = "$class-$this->owner->ID";
        }

        return $t;
    }

    public function makeURLSegmentUnique()
    {
        // Ensure that this object has a non-conflicting URLSegment value.
        $count = 2;

        $URLSegment = $this->owner->URLSegment;

        while ($this->URLSegmentInUse($URLSegment)) {
            $URLSegment = preg_replace('/-[0-9]+$/', null, $URLSegment) . '-' . $count;
            ++$count;
        }

        $this->owner->URLSegment = $URLSegment;
    }
}
