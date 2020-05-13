<?php

namespace JonoM\Helpers\Fields;

use SilverStripe\Forms\OptionsetField;

/**
 * Create an option set field with 'Yes' and 'No' fields.
 * Alternative to a CheckboxField. Arguably looks better because you get a regular field label this way.
 * Optionally customise the yes/no labels.
 */
class BooleanField extends OptionsetField
{
    public function __construct($name, $title = null, $trueLabel = 'Yes', $falseLabel = 'No')
    {
        parent::__construct($name, $title);
        $this->setSource([
            1 => $trueLabel,
            0 => $falseLabel
        ]);
    }

    /**
     * Style like an optionset field
     */
    public function Type()
    {
        return 'optionset';
    }
}
