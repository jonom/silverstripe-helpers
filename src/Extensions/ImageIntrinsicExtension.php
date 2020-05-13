<?php

namespace JonoM\Helpers\Extensions;

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;

class ImageIntrinsicExtension extends DataExtension
{
    private static $casting = [
        'Intrinsic' => 'HTMLText',
    ];

    /**
     * Start of image optimisation strategy.
     * ToDo: image sets (responsive images), and lazy loading.
     */
    public function PaddingBottom()
    {
        // Get padding ratio for use with this technique: http://www.smashingmagazine.com/2013/09/16/responsive-images-performance-problem-case-study/
        // Mostly copied from https://github.com/Moosylvania/SilverStripe-Responsive-Image/blob/master/code/ResponsiveImage.php
        // Use in templates like style='padding-bottom:{$PaddingBottom}%'
        $w = $this->owner->getWidth();
        $h = $this->owner->getHeight();
        if (!$w || !$h) {
            return false;
        }
        $ratio = $h / $w;

        return round($ratio * 100, 10);
    }
    /**
     * Return markup for intrinsic ratio image.
     *
     * @param string $wrapperElement what element to wrap around the image
     * @param string $lazy Lazy-load with Unveil.js
     * @return string
     */
    public function Intrinsic($lazy = false, $wrapperElement = 'div')
    {
        return $this->owner->renderWith('ImageIntrinsic', [
            'WrapperElement' => $wrapperElement,
            'ImageTag' => $this->owner->renderWith('Image', [
                'Lazy' => $lazy
            ])
        ]);
    }

    public function IntrinsicSrcSet($minWidth, $maxWidth, $lazy = false, $stepMultiplier = 1.2, $wrapperElement = 'div')
    {
        return $this->owner->renderWith('ImageIntrinsic', [
            'WrapperElement' => $wrapperElement,
            'ImageTag' => $this->SrcSet($minWidth, $maxWidth, $lazy, $stepMultiplier)
        ]);
    }

    /**
     * Generate an img tag with a range of options
     * Increase the width by set percent until max width reached.
     *
     * @param int $minWidth
     * @param int $maxWidth
     * @param int $stepIncrease
     * @param float $stepMultiplier
     * @param bool $intrinsic
     */
    public function SrcSet($minWidth, $maxWidth, $lazy = false, $stepMultiplier = 1.2)
    {
        if ((int) $minWidth < 1 || (int) $maxWidth < 1 || $this->owner->getWidth() < 1) {
            return false;
        }
        $images = ArrayList::create();
        // Generate the images, starting with the minimum
        $width = $minWidth;
        while ($width < $maxWidth && $width < $this->owner->getWidth()) {
            $images->push($this->owner->ScaleWidth($width));
            $width = ceil($width * $stepMultiplier);
        }
        // Add an image set at max width
        $images->push($this->owner->ScaleMaxWidth($maxWidth));
        // Render as an img tag
        if ($images->count()) {
            return $images->renderWith('ImageSrcSet', [
                'Lazy' => $lazy
            ]);
        }
    }

    /**
     * Get a linear gradient style string that roughly matches image colours.
     * Uses a horizontal gradient for landscape images, vertical otherwise.
     */
    public function LazyBGColorStyle()
    {
        $horiz = ($this->owner->getWidth() > $this->owner->getHeight());
        $bgSource = $this->BGGradientSource($horiz);
        if (!$bgSource) {
            return false;
        }
        $img = imagecreatefromstring($bgSource->getString());
        $colors = [];
        $colors[] = ImageColorAt($img, 0, 0);
        $colors[] = $horiz ? ImageColorAt($img, 1, 0) : ImageColorAt($img, 0, 1);
        foreach ($colors as &$rgb) {
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $rgb = "rgb($r,$g,$b)";
        }
        $toRight = ($horiz) ? 'to right, ' : '';
        return "background: $colors[0]; background: linear-gradient($toRight$colors[0], $colors[1]);";
    }

    public function BGGradientSource($horiz = false)
    {
        return ($horiz) ? $this->owner->ResizedImage(2, 1) : $this->owner->ResizedImage(1, 2);
    }

    public function BGBasicSource()
    {
        return $this->owner->Fit(4, 4);
    }

    public function BGBasicSourceEncoded()
    {
        $bgImg = $this->BGBasicSource();
        $data = $bgImg->getString();
        $type = $bgImg->getMimeType();
        return 'data:' . $type . ';base64,' . base64_encode($data);
    }
}
