<?php

namespace JonoM\Helpers\Utility;

use SilverStripe\Control\Director;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Environment;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\TemplateGlobalProvider;

/**
 * Some additional functions to be available in all templates.
 * Note that this is not an extension. TemplateGlobalProvider can be implemented
 * on any class to allow indicated static methods to be available in all templates.
 */
class TemplateFunctionAdditions implements TemplateGlobalProvider
{
    public static function IsDev()
    {
        // Expose dev mode status to templates
        return Director::isDev();
    }

    /**
     * Generate a cache key for the current environment.
     *
     * @return string
     */
    public static function EnvCacheKey()
    {
        return __FUNCTION__ . md5(serialize([
            // Dev mode can affect output
            Director::get_environment_type(),
            // Split cache by protocol to prevent accidental mixed-content issues
            Director::protocolAndHost(),
            // Site Config may affect e.g. page titles
            SiteConfig::get()->max('LastEdited'),
        ]));
    }

    public static function TodayCacheKey()
    {
        // Reset cache every day to ensure date based content is accurate
        return __FUNCTION__ . date('Y-m-d');
    }

    /**
     * Get a cache key which get the approximate state of a class.
     * Will not capture things like sort order if changes bypass the ORM.
     *
     * @param string $class
     * @return string
     */
    public static function ClassCacheKey($class)
    {
        return __FUNCTION__ . md5(serialize([
            $class,
            $class::get()->max('LastEdited'), // Catch created / edited
            $class::get()->count(), // Catch deleted
        ]));
    }

    /**
     * Use on a ManyManyList or potentially a DataList HasManyList. Generate a cache key that covers:
     * - Which items are in this list
     * - The sort order of these items
     * - The most recent LastEdited value
     *
     * Note that for a DataList or HasManyList ClassCacheKey() should generally be sufficient to invalidate when something changes.
     *
     * @param mixed $dataList
     * @return void
     */
    public static function ListCacheKey($dataList)
    {
        return md5(serialize([
            // Namespace for this cacheblock
            $dataList->dataClass(),
            // This covers which objects are linked and their sort order
            implode('-', $dataList->column('ID')),
            // This catches edits
            $dataList->max('LastEdited'),
        ]));
    }

    /**
     * Caching can be temporarily disabled by setting SS_DISABLE_PARTIAL_CACHING to true in ss_environment.php
     * Add any logical tests here that should prevent caching.
     */
    public static function DontCache()
    {
        return Environment::getEnv('SS_DISABLE_PARTIAL_CACHING');
    }

    /**
     * Get the first dataobject of a particular type.
     * Useful for creating a link to a page of a specific class.
     *
     * @param string $className
     *
     * @return DataObject|false
     */
    public static function Single($className)
    {
        return ClassInfo::exists($className)
            ? $className::get()->first()
            : false;
    }

    public static function get_template_global_variables()
    {
        return [
            'IsDev' => 'IsDev',
            'EnvCacheKey' => 'EnvCacheKey',
            'TodayCacheKey' => 'TodayCacheKey',
            'ClassCacheKey' => 'ClassCacheKey',
            'ListCacheKey' => 'ListCacheKey',
            'DontCache' => 'DontCache',
            'ParamCacheKey' => 'ParamCacheKey',
            'Single' => 'Single',
        ];
    }
}
