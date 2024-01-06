<?php

namespace Fromholdio\SVGenius\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;

class SVGImageFormFactoryExtension extends Extension
{
    public function updateFormFields(FieldList $fields, $controller, $formName, $context)
    {
        $image = $context['Record'] ?? null;
        if ($image?->getIsSVG()) {
            $fields->removeByName([
                'FocusPoint',
                'FocusPointTab'
            ]);
        }
    }
}
