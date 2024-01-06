<?php

namespace Fromholdio\SVGenius\Model;

use Fromholdio\SVGenius\SVGeniusTrait;
use SilverStripe\Assets\Image;
use SilverStripe\Assets\Storage\AssetContainer;
use SVG\SVG as SVGParser;

class SVGImage extends Image
{
    use SVGeniusTrait;

    private static $table_name = 'SVGImage';
    private static $singular_name = 'SVG image';
    private static $plural_name = 'SVG images';

    public function __construct($record = null, $isSingleton = false, $queryParams = [])
    {
        parent::__construct($record, $isSingleton, $queryParams);
        if ($this->File->exists()) {
            $this->initSVGFromString($this->File->getString(), false);
        }
    }

    public function onBeforeWrite(): void
    {
        $sanitisedString = $this->sanitiseSVGString($this->File->getString());
        $this->File->setFromString(
            $sanitisedString,
            $this->File->getFilename()
        );
        $this->svg = SVGParser::fromString($sanitisedString);
        parent::onBeforeWrite();
    }


    public function getImageTag(): string
    {
        return parent::getTag();
    }

    public function Thumbnail($width, $height): AssetContainer
    {
        return $this->resizeByRatio($width, $height);
    }

    public function manipulate($variant, $callback): AssetContainer
    {
        return $this;
    }

    public function existingOnly(): AssetContainer
    {
        return $this->setAllowGeneration(false);
    }

    public function Resampled(): AssetContainer
    {
        return $this;
    }

    public function Quality($quality): AssetContainer
    {
        return $this;
    }
}
