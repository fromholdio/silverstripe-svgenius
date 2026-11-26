<?php

namespace Fromholdio\SVGenius\Extensions;

use SilverStripe\Core\Extension;

class SVGEditImageFormFactoryExtension extends Extension
{
    protected function updatePopoverActions(&$actions, $record): void
    {
        if ($record?->getIsSVG()) {
            $actions = array_filter(
                $actions,
                fn ($action) => $action?->getName() !== 'action_editimage'
            );
        }
    }
}
