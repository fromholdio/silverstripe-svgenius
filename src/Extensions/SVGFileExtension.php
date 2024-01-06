<?php

namespace Fromholdio\SVGenius\Extensions;

use SilverStripe\ORM\DataExtension;

class SVGFileExtension extends DataExtension
{
    public function IsSVG(): bool
    {
        return $this->getOwner()->getIsSVG();
    }

    public function getIsSVG(): bool
    {
        return $this->getOwner()->getExtension() === 'svg';
    }
}
