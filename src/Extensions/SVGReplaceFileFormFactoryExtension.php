<?php

namespace Fromholdio\SVGenius\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;

class SVGReplaceFileFormFactoryExtension extends Extension
{
    protected function updateFormFields(FieldList $fields, $controller, $formName, $context): void
    {
        $image = $context['Record'] ?? null;
        if ($image?->getIsSVG()) {
            $fields->fieldByName('PreviewImage')?->performReadonlyTransformation();
        }
    }

    protected function updatePopoverActions(&$actions, $record): void
    {
        if ($record?->getIsSVG()) {
            $actions = array_filter(
                $actions,
                fn ($action) => $action?->getName() !== 'action_replacefile'
            );
        }
    }
}
