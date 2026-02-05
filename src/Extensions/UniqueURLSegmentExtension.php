<?php

namespace JonoM\Helpers\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\View\Parsers\URLSegmentFilter;

class UniqueURLSegmentExtension extends Extension
{
    private static $db = [
        'URLSegment' => 'Varchar(255)',
    ];

    public function onBeforeWrite()
    {
        // If there is no URLSegment set, generate one from Title
        if (!$this->getOwner()->URLSegment) {
            $this->getOwner()->URLSegment = $this->generateURLSegment($this->getOwner()->getTitle());
        }

        // validate segment or create default
        if (!$this->getOwner()->isInDB() || $this->getOwner()->isChanged('URLSegment')) {
            $this->getOwner()->URLSegment = $this->generateURLSegment($this->getOwner()->URLSegment);
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
        if ($this->getOwner()->ID > 0) {
            $items = $items->exclude('ID', $this->getOwner()->ID);
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
            $class = strtolower($this->getOwner()->ClassName);
            $t = "$class-{$this->getOwner()}->ID";
        }

        return $t;
    }

    public function makeURLSegmentUnique()
    {
        // Ensure that this object has a non-conflicting URLSegment value.
        $count = 2;

        $URLSegment = $this->getOwner()->URLSegment;

        while ($this->URLSegmentInUse($URLSegment)) {
            $URLSegment = preg_replace('/-[0-9]+$/', null, $URLSegment) . '-' . $count;
            ++$count;
        }

        $this->getOwner()->URLSegment = $URLSegment;
    }
}
