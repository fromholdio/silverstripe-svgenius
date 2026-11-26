<?php

namespace Fromholdio\SVGenius\Extensions;

use SilverStripe\Core\Extension;

class SVGFileExtension extends Extension
{
    public function IsSVG(): bool
    {
        return $this->getOwner()->getIsSVG();
    }

    public function getIsSVG(): bool
    {
        return strtolower($this->getOwner()->getExtension()) === 'svg';
    }
}
