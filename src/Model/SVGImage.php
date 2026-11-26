<?php

namespace Fromholdio\SVGenius\Model;

use Fromholdio\SVGenius\SVGeniusTrait;
use SilverStripe\Assets\Image;
use SilverStripe\Assets\Storage\AssetContainer;
use SilverStripe\Assets\Util;
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

    public function setFromLocalFile($path, $filename = null, $hash = null, $variant = null, $config = []): array
    {
        if (!is_readable($path ?? '')) {
            return parent::setFromLocalFile($path, $filename, $hash, $variant, $config);
        }
        return $this->setFromString(file_get_contents($path), $filename ?: basename($path), $hash, $variant, $config);
    }

    public function setFromStream($stream, $filename, $hash = null, $variant = null, $config = []): array
    {
        Util::rewindStream($stream);
        return $this->setFromString(stream_get_contents($stream), $filename, $hash, $variant, $config);
    }

    public function setFromString($data, $filename, $hash = null, $variant = null, $config = []): array
    {
        return parent::setFromString($this->sanitiseSVGString($data), $filename, $variant ? $hash : null, $variant, $config);
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
