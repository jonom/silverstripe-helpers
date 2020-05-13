<?php

namespace JonoM\Helpers\Extensions;

use SilverStripe\Core\Extension;

/**
 * Apply to DataList. Generate a cache key that covers:
 * - Which items are in this list
 * - The sort order of these items
 * - The most recent LastEdited value
 *
 */
class DataListCacheKeyExtension extends Extension
{
    public function CacheKey($prefix = null)
    {
        if (!$prefix) {
            $prefix = $this->owner->dataClass();
        }
        $fragments = [
            // Namespace for this cacheblock
            $prefix,
            // This covers which objects are linked and their sort order
            implode('-', $this->owner->Column('ID')),
            // This catches edits
            $this->owner->Max('LastEdited'),
        ];
        return implode('-_-', $fragments);
    }
}
