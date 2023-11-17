<?php

namespace JonoM\Helpers\Extensions;

use SilverStripe\Control\Director;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Extension;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\Requirements;
use SilverStripe\View\SSViewer;

class PageControllerHelpersExtension extends Extension
{
    private static $allowed_actions = [];

    /**
     * Get a cache key which represents the approximate state of a page
     *
     * @param string $class
     * @return string
     */
    public function PageCacheKey()
    {
        return __FUNCTION__ . md5(serialize([
            static::class,
            // Identify by ID, or URL as a fallback. Note that Security pages (and maybe others?) generate a random ID so the fallback may be redundant
            $this->owner->ID ?: $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
            $this->owner->request->params(),
            $this->owner->LastEdited,
        ]));
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
     * Get closest existing value for a field or relation from this page or any ancestor, ending at SiteConfig.
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
        $siteConfig = SiteConfig::current_site_config();
        return $siteConfig->hasValue($property) ? $siteConfig->cachedCall($property) : false;
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
     * Inset Google Analytics head script tags if the SS_GOOGLE_ANALYTICS_ACCOUNT_ID env var is set.
     *
     * @return void
     */
    public function requireGAScriptTags()
    {
        $accountId = Environment::getEnv('SS_GOOGLE_ANALYTICS_ACCOUNT_ID');

        if (preg_match('/UA-[0-9]{6,}-[0-9]{1,}/', $accountId)) {
            // Global site tag (gtag.js) - Google Analytics
            $tags = <<<HTML
<script async src="https://www.googletagmanager.com/gtag/js?id=$accountId"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '$accountId');
</script>
HTML;
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
