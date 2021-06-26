<?php

namespace JonoM\Helpers\Extensions;

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBField;

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

        // Prevent loss of resampled image. Workaround for https://github.com/silverstripe/silverstripe-assets/commit/03d38f2a817f970b6e75cc6a44e784b0e2e9eae4
        $backend = $this->owner->getImageBackend();
        $originalResource = $backend->getImageResource();

        while ($width < $maxWidth && $width < $this->owner->getWidth()) {
            $images->push($this->owner->ScaleWidth($width));
            $width = ceil($width * $stepMultiplier);
            $backend->setImageResource($originalResource);
        }
        // Add an image set at max width
        $images->push($this->owner->ScaleMaxWidth($maxWidth));

        // Reset the resource
        $backend->setImageResource($originalResource);

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
        return $this->BGBasicSource()->Base64Url();
    }

    public function Base64Url()
    {
        if (!$this->owner->exists()) return;
        $data = base64_encode($this->owner->getString());
        $type = $this->owner->getMimeType();
        return "data:$type;base64,$data";
    }

    public function SummaryThumbnail()
    {
        // Generated with https://placeholderimage.dev/
        $placeholderHtml = '<img data-v-6d3ac3b0="" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAC2klEQVRoQ+2YC08aQRSFzyygsLuwIBQsD3n4KIm19v//iqY1rVVLBSK+qqIusMtjt7lTsdqkukydxNiZZBPgcu/cc747QwI76do+XsBiSsgzo6iIPDMgUEQUEUkOqNGSZKxwWUVE2DpJiYqIJGOFyyoiwtZJSlREJBkrXFYREbZOUuL/R2SvdQjGAN8HegOH+2qZOiqFHH898Tx8ax1i4A7h+z7mImEe06Pz/PuN9hGy6SROzy8xnkwQDoVQKy3yOC2776DZOcZ44kHTGNJWHPlsOjC/wEQ+f2thOBqDMcYFXNp93nC1mINlGpjGkwkTIY3h/NLmTWyslmHfCKH31Dj9bTNwXEQiYawvL8EZjrDdaPPaCVOH6w75Z69SFgq5YGJmFrK+soRIOAx3OMKXRptvZsV1EDESUclnuQAS0jo8QS6dhKFHOZG4EcNy6TWPf90/gOMOsfmmit1WB72+g3q1iOj8HI9v7TXheT7erVUCUZlJCI3Pxurvwh+2G7z56FwERz8uUC3kYMUNvjE18XHnOxKGjsxCggspLWaQTiZ4fP/gGN3rHt7Xa9jabfJx02O/xoyW447geR6PB1kzCaFRWl8p39adCglpGs66V6jXSlzU9Mx82tm/J4RokfA/hZBgEj6lcbdxohRkPYkQIzaPg+MzLGZS/KF1cWWj2TlBdsGCacQ4kb8JmZ6vzXoN7KZrIkyjN71MHhPzJEJoZGg8NMawspTnt85eq4PReIK3q2X0HfdBIdQ0PWQI3VR0vogwXQxrlcJjGnj8n4WkEibK+SzOutdoH53e27SYyyCTSuCq139QCCXRZWH3B7f5dD3Xa0V+TQdZgYUEKUaH87o3ANMYTD3GCc2y6Hqn3xM6Z3cPfpAaTyokyIayvqOEyHJWtK4iIuqcrDxFRJazonUVEVHnZOUpIrKcFa2riIg6JytPEZHlrGhdRUTUOVl5iogsZ0XrvhgiPwGa8AJb/jK2WwAAAABJRU5ErkJggg==" alt="none">';
        return $this->owner->exists() ? $this->owner->FocusFill(50, 50) : DBField::create_field('HTMLText', $placeholderHtml);
    }
}
