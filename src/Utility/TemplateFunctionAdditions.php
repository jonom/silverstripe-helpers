<?php

namespace JonoM\Helpers\Utility;

use SilverStripe\Control\Director;
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

    public static function get_template_global_variables()
    {
        return [
            'IsDev' => 'IsDev'
        ];
    }
}
