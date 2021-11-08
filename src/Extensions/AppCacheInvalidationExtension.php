<?php

namespace JonoM\Helpers\Extensions;

use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataExtension;

/**
 * Apply this extension to a class to have it invalidate a given cache each time an object of this class changes.
 *
 * Usage e.g.:
 *
 *    private static $extensions = [
 *        'JonoM\Helpers\Extensions\AppCacheInvalidationExtension("menuData","myAppData")',
 *    ];
 */
class AppCacheInvalidationExtension extends DataExtension
{
    protected $cacheNamespaces = [];

    public function __construct()
    {
        $this->cacheNamespaces = func_get_args();
    }

    /**
     * @return void
     */
    public function purgeCache()
    {
        foreach ($this->cacheNamespaces as $namespace) {
            $cache = Injector::inst()->get(CacheInterface::class . "." . $namespace);
            $cache->clear();
        }
    }

    /**
     * @return void
     */
    public function onAfterPublish()
    {
        $this->purgeCache();
    }

    /**
     * @return void
     */
    public function onAfterUnpublish()
    {
        $this->purgeCache();
    }

    /**
     * @return void
     */
    public function onAfterWrite()
    {
        $this->purgeCache();
    }

    /**
     * @return void
     */
    public function onAfterReorder()
    {
        $this->purgeCache();
    }

    /**
     * @return void
     */
    public function onAfterDelete()
    {
        $this->purgeCache();
    }

    /**
     * @return void
     */
    public function onAfterManyManyRelationAdd()
    {
        $this->purgeCache();
    }

    /**
     * @return void
     */
    public function onAfterManyManyRelationRemove()
    {
        $this->purgeCache();
    }
}
