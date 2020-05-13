<?php

namespace JonoM\Helpers\Extensions;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Director;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;
use SilverStripe\View\SSViewer;

class PageControllerHelpersExtension extends Extension
{
    private static $allowed_actions = [];

    /**
     * Build a cache key suitable for general page content.
     * Caution:
     * - Not suitable for non-page content (datalists) or user-customised content.
     * - Doesn't include GET or POST vars so supplement key as required, e.g. for pages with pagination or search queries
     *
     * Supplement this cache key with additional parameters in the cache block where necessary, such as $CurrentMember.ID or $List('SomeClass').Max('LastEdited').
     *
     * Partial caching of nested relationships (loop within loop) is impractical, so exclude those from partial caching and cache dynamically only.
     *
     * Example of using extension:
     *     public function updatePageCacheKey(&$fragments) {
     *         $fragments[] = $this->owner->Query();
     *     }
     *
     * @access public
     * @return string
     */
    public function PageCacheKey()
    {
        $fragments = [];
        // Start with the class
        $fragments[] = $this->owner->ClassName;
        // Identify by ID, or URL as a fallback. Note that Security pages (and maybe others?) generate a random ID so this may be redundant
        $fragments[] = $this->owner->ID ? $this->owner->ID : $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        // Identify the action and ID
        $fragments[] = json_encode($this->owner->request->params());
        // Reset cache every day to ensure date based content is accurate
        //$fragments[] = date('Y-m-d');
        // Ensure menus are up to date
        $fragments[] = SiteTree::get()->max('LastEdited'); // Catch created / edited
        $fragments[] = SiteTree::get()->count(); // Catch deleted
        // Dev mode can affect output
        $fragments[] = Director::get_environment_type();
        // Split cache by protocol to prevent accidental mixed-content issues
        $fragments[] = Director::protocol();

        // Extension hook
        $this->owner->extend('updatePageCacheKey', $fragments);

        return md5(serialize($fragments));
    }

    /**
     * Caching can be temporarily disabled by setting SS_DISABLE_PARTIAL_CACHING to true in ss_environment.php
     * Add any logical tests here that should prevent caching.
     */
    public function DontCache()
    {
        $cache = Environment::getEnv('SS_DISABLE_PARTIAL_CACHING');

        // Extension hook
        $this->owner->extend('updatePageDontCache', $cache);

        return $cache;
    }

    /**
     * Provide a cache key string for a given POST or GET var. Provide one or more var names as arguments.
     *
     * @access public
     * @param string $name
     * @return string
     */
    public function ParamCacheKey($name)
    {
        $args = func_get_args();
        foreach ($args as &$name) {
            $name = $name . '-' . $this->owner->request->requestVar($name);
        }
        return implode('_', $args);
    }

    /**
     * Get closest existing value for a field or relation from this page or any ancestor.
     * This allows you to make values and objects cascade down through children with the option to override.
     *
     * @access public
     * @param string $property Name of Field, Method or Relation
     * @return mixes
     */
    public function Closest($property)
    {
        $page = $this->owner;
        while ($page && $page->exists()) {
            if ($page->hasValue($property)) {
                return $page->cachedCall($property);
            }
            // Go up a level if no result yet
            $page = $page->Parent();
        }
        return false;
    }

    /**
     * Get the first dataobject of a particular type.
     * Useful for creating a link to a page of a specific class.
     *
     * @param string $className
     *
     * @return DataObject|false
     */
    public function Single($className)
    {
        return ClassInfo::exists($className)
            ? $className::get()->first()
            : false;
    }

    /**
     * A list of classes this Page extends from. Apply to body tag for CSS targetting.
     *
     * @return string
     */
    public function ClassAncestors()
    {
        $ancestorClasses = $this->owner->getClassAncestry();
        // We only want the ancestors until Page is reached
        $pageKey = array_search('page', array_keys($ancestorClasses));
        return implode(' ', array_slice($ancestorClasses, $pageKey));
    }

    /**
     * Inset Google Analtytics head script tags if the SS_GOOGLE_ANALYTICS_ACCOUNT_ID env var is set.
     *
     * @return void
     */
    public function requireGAScriptTags()
    {
        $accountId = Environment::getEnv('SS_GOOGLE_ANALYTICS_ACCOUNT_ID');

        if (preg_match('/UA-[0-9]{6,}-[0-9]{1,}/', $accountId)) {
            // Global site tag (gtag.js) - Google Analytics
            $tags = <<<JS
<script async src="https://www.googletagmanager.com/gtag/js?id=$accountId"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '$accountId');
</script>
JS;
            Requirements::insertHeadTags($tags);
        }
    }

    /**
     * Turn on template comments when in dev mode.
     * Should only be applied to front-end controllers because these comments can break parts of the CMS.
     *
     * @return void
     */
    public function enableTemplateComments()
    {
        // Turn on template comments. We don't want to do this on all controllers because it can break parts of the CMS.
        if (Director::isDev()) {
            SSViewer::config()->set('source_file_comments', true);
        }
    }

    public function LogoHeadingLevel()
    {
        return ($this->owner->URLSegment == 'home') ? 1 : 2;
    }
}
