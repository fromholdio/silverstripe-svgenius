<?php

namespace Fromholdio\SVGenius;

use enshrined\svgSanitize\Sanitizer;
use SilverStripe\Assets\Storage\AssetContainer;
use SVG\SVG as SVGParser;

trait SVGeniusTrait
{
    private static $casting = [
        'Inline' => 'HTMLText',
        'Tag' => 'HTMLFragment',
        'getTag' => 'HTMLFragment',
        'ImageTag' => 'HTMLFragment',
        'getImageTag' => 'HTMLFragment',
    ];

    private ?SVGParser $svg;

    protected ?int $width = null;
    protected ?int $height = null;
    protected array $extraCSSClasses = [];


    protected function initSVGFromPath(string $path, bool $doSanitise = true): void
    {
        $string = file_exists($path) ? SVGParser::fromFile($path)->toXMLString() : null;
        $this->initSVGFromString($string, $doSanitise);
    }

    protected function initSVGFromString(?string $string, bool $doSanitise = true): void
    {
        if (empty($string)) {
            $this->svg = null;
        }
        else {
            if ($doSanitise) {
                $string = $this->sanitiseSVGString($string);
            }
            $this->svg = SVGParser::fromString($string);
        }
    }

    protected function sanitiseSVGString(?string $string): ?string
    {
        if (empty($string)) return null;
        $sanitiser = new Sanitizer();
        return $sanitiser->sanitize($string);
    }


    public function getInline(): ?string
    {
        if (empty($this->svg)) return null;

        $svg = $this->svg;
        $doc = $svg->getDocument();
        $doc->setWidth($this->getWidth());
        $doc->setHeight($this->getHeight());
        $extraCSSClasses = $this->getExtraCSSClasses();
        if (!empty($extraCSSClasses)) {
            $doc->setAttribute('class', $extraCSSClasses);
        }
        $doc->removeAttribute('id');

//        $token = Tokenator::generate_tokenator(5);
//        $title = $this->getField('Title') ?? 'My title';
//        $altText = $this->getField('AltText') ?? 'asdf asdlf asdfklj';
//        $titleNode = new SVGTitle($title);
//        $titleNode->setAttribute('id', $token . '-id');
//        $descNode = new SVGDesc();
//        $descNode->setValue($altText);
//        $descNode->setAttribute('id', $token . '-desc');
//        $doc->setAttribute('aria-labelledby', $token . '-id ' . $token . '-desc');
//        $doc->setAttribute('role', 'img');
//        $doc->addChild($descNode, 0);
//        $doc->addChild($titleNode, 0);

        return $svg->toXMLString();
    }

    public function getTag()
    {
        if (empty($this->svg)) return null;
        return (string) $this->renderWith('DBFile_svg');
    }

    public function getImageTag()
    {
        if (empty($this->svg)) return null;
        return parent::getTag();
    }


    public function addExtraCSSClass(string $class): self
    {
        $this->extraCSSClasses[$class] = $class;
        return $this;
    }

    public function setExtraCSSClasses(string|array $classes): self
    {
        if (is_string($classes)) {
            $classes = explode(' ', $classes);
        }
        $this->extraCSSClasses = [];
        foreach ($classes as $class) {
            $this->addExtraCSSClass($class);
        }
        return $this;
    }

    public function removeExtraCSSClass(string $class): self
    {
        unset($this->extraCSSClasses[$class]);
        return $this;
    }

    public function getExtraCSSClassesArray(): array
    {
        return $this->extraCSSClasses;
    }

    public function getExtraCSSClasses(): ?string
    {
        return implode(' ', array_values($this->getExtraCSSClassesArray()));
    }


    public function getDimensions(): array
    {
        return [
            $this->getWidth(),
            $this->getHeight()
        ];
    }

    public function getWidth(): int
    {
        return $this->width ?? (int) $this->svg?->getDocument()->getWidth();
    }

    public function getHeight(): int
    {
        return $this->height ?? (int) $this->svg?->getDocument()->getHeight();
    }


    protected function resize($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
        return $this;
    }

    protected function resizeByWidth($width)
    {
        list($currWidth, $currHeight) = $this->getDimensions();
        if ($currWidth <= 0 || $width <= 0) {
            return $this;
        }
        $ratio = $width / $currWidth;
        $newHeight = $currHeight * $ratio;
        return $this->resize($width, $newHeight);
    }

    protected function resizeByHeight($height)
    {
        list($currWidth, $currHeight) = $this->getDimensions();
        if ($currHeight <= 0 || $height <= 0) {
            return $this;
        }
        $ratio = $height / $currHeight;
        $newWidth = $currWidth * $ratio;
        return $this->resize($newWidth, $height);
    }

    protected function resizeRatio($width, $height)
    {
        list($currWidth, $currHeight) = $this->getDimensions();
        if ($currWidth <= 0 || $width <= 0) {
            return $this;
        }
        if ($currHeight <= 0 || $height <= 0) {
            return $this;
        }

        $widthRatio = $width / $currWidth;
        $heightRatio = $height / $currHeight;

        if ($widthRatio < $heightRatio && $currWidth === $width) {
            return $this;
        }
        elseif ($currHeight === $heightRatio) {
            return $this;
        }

        $dominantWidth_height = $currHeight * $widthRatio;
        if ($dominantWidth_height <= $height) {
            return $this->ResizedImage($width, $dominantWidth_height);
        }

        $dominantHeight_width = $currWidth * $heightRatio;
        if ($dominantHeight_width <= $width) {
            return $this->ResizedImage($dominantHeight_width, $height);
        }

        return $this;
    }


    public function ResizedImage($width, $height)
    {
        return $this->resize($width, $height);
    }

    /**
     * Scale image proportionally to fit within the specified bounds
     *
     * @param int $width The width to size within
     * @param int $height The height to size within
     * @return AssetContainer
     */
    public function Fit($width, $height)
    {
        return $this->resizeRatio($width, $height);
    }

    /**
     * Proportionally scale down this image if it is wider or taller than the specified dimensions.
     * Similar to Fit but without up-sampling. Use in templates with $FitMax.
     *
     * @uses ScalingManipulation::Fit()
     * @param int $width The maximum width of the output image
     * @param int $height The maximum height of the output image
     * @return AssetContainer
     */
    public function FitMax($width, $height)
    {
        return $this->Fit($width, $height);
    }

    /**
     * Scale image proportionally by width. Use in templates with $ScaleWidth.
     *
     * @param int $width The width to set
     * @return AssetContainer
     */
    public function ScaleWidth($width)
    {
        return $this->resizeByWidth($width);
    }

    /**
     * Proportionally scale down this image if it is wider than the specified width.
     * Similar to ScaleWidth but without up-sampling. Use in templates with $ScaleMaxWidth.
     *
     * @uses ScalingManipulation::ScaleWidth()
     * @param int $width The maximum width of the output image
     * @return AssetContainer
     */
    public function ScaleMaxWidth($width)
    {
        return $this->ScaleWidth($width);
    }

    /**
     * Scale image proportionally by height. Use in templates with $ScaleHeight.
     *
     * @param int $height The height to set
     * @return AssetContainer
     */
    public function ScaleHeight($height)
    {
        return $this->resizeByHeight($height);
    }

    /**
     * Proportionally scale down this image if it is taller than the specified height.
     * Similar to ScaleHeight but without up-sampling. Use in templates with $ScaleMaxHeight.
     *
     * @uses ScalingManipulation::ScaleHeight()
     * @param int $height The maximum height of the output image
     * @return AssetContainer
     */
    public function ScaleMaxHeight($height)
    {
        return $this->ScaleHeight($height);
    }


    // Not done;

    /**
     * Resize and crop image to fill specified dimensions.
     * Use in templates with $Fill
     *
     * @param int $width Width to crop to
     * @param int $height Height to crop to
     * @return AssetContainer
     */
    public function Fill($width, $height)
    {
        return $this->Fit($width, $height);
    }

    /**
     * Crop this image to the aspect ratio defined by the specified width and height,
     * then scale down the image to those dimensions if it exceeds them.
     * Similar to Fill but without up-sampling. Use in templates with $FillMax.
     *
     * @uses ImageManipulation::Fill()
     * @param int $width The relative (used to determine aspect ratio) and maximum width of the output image
     * @param int $height The relative (used to determine aspect ratio) and maximum height of the output image
     * @return AssetContainer
     */
    public function FillMax($width, $height)
    {
        return $this->Fill($width, $height);
    }

    /**
     * Crop image on X axis if it exceeds specified width. Retain height.
     * Use in templates with $CropWidth. Example: $Image.ScaleHeight(100).$CropWidth(100)
     *
     * @uses CropManipulation::Fill()
     * @param int $width The maximum width of the output image
     * @return AssetContainer
     */
    public function CropWidth($width)
    {
        return $this->ScaleWidth($width);
    }

    /**
     * Crop image on Y axis if it exceeds specified height. Retain width.
     * Use in templates with $CropHeight. Example: $Image.ScaleWidth(100).CropHeight(100)
     *
     * @uses CropManipulation::Fill()
     * @param int $height The maximum height of the output image
     * @return AssetContainer
     */
    public function CropHeight($height)
    {
        return $this->ScaleHeight($height);
    }

    public function FocusFill(int $width, int $height)
    {
        return $this->Fill($width, $height);
    }

    public function FocusFillMax(int $width, int $height)
    {
        return $this->FillMax($width, $height);
    }

    public function FocusCropWidth(int $width, int $height)
    {
        return $this->CropWidth($width, $height);
    }

    public function FocusCropHeight(int $width, int $height)
    {
        return $this->CropHeight($width, $height);
    }
}
