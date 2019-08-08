<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Thumbnail;

use Camelot\ImageAsset\Filesystem\Image;
use Camelot\ImageAsset\Image\Attributes\Color;
use Camelot\ImageAsset\Image\Attributes\Point;
use Camelot\ImageAsset\Image\ImageResource;
use Camelot\ImageAsset\Transaction\JobInterface;
use Contao\ImagineSvg\Imagine as SvgImagine;
use Imagine\Image\Box;

final class Resizer
{
    /** @var SvgImagine */
    private $svgImagine;
    /** @var Color */
    private $background;

    public function __construct(SvgImagine $svgImagine, Color $background)
    {
        $this->svgImagine = $svgImagine;
        $this->background = $background;
    }

    /** Do the actual resize/crop/fit/border logic and return the image data. */
    public function resize(JobInterface $job): ?string
    {
        /** @var Image $requestImage */
        $requestImage = $job->getRequestImage();
        $img = ImageResource::createFromString($requestImage->read());

        $target = clone $job->getTargetDimensions();
        $original = $img->getDimensions();
        $point = new Point();
        $crop = $job->getAction()->isCrop();
        $fit = $job->getAction()->isFit();
        $border = $job->getAction()->isBorder();

        if ($crop) {
            $xRatio = $original->getWidth() / $target->getWidth();
            $yRatio = $original->getHeight() / $target->getHeight();

            // calculate x or y coordinate and width or height of source
            if ($xRatio > $yRatio) {
                $point->setX((int) round(($original->getWidth() - ($original->getWidth() / $xRatio * $yRatio)) / 2));
                $original->setWidth((int) round($original->getWidth() / $xRatio * $yRatio));
            } elseif ($yRatio > $xRatio) {
                $point->setY((int) round(($original->getHeight() - ($original->getHeight() / $yRatio * $xRatio)) / 2));
                $original->setHeight((int) round($original->getHeight() / $yRatio * $xRatio));
            }
        } elseif (!$border && !$fit) {
            $ratio = min($target->getWidth() / $original->getWidth(), $target->getHeight() / $original->getHeight());
            $target->setWidth((int) ($original->getWidth() * $ratio));
            $target->setHeight((int) ($original->getHeight() * $ratio));
        }

        $new = ImageResource::createNew($target, $img->getType());
        if ($border) {
            $new->fill($this->background);

            $scaled = $original->getHeight() * ($target->getWidth() / $original->getWidth());
            if ($scaled > $target->getHeight()) {
                $target->setWidth((int) ($original->getWidth() * ($target->getHeight() / $original->getHeight())));
                $point->setX((int) round(($job->getTargetDimensions()->getWidth() - $target->getWidth()) / 2));
            } else {
                $target->setHeight((int) $scaled);
                $point->setY((int) round(($job->getTargetDimensions()->getHeight() - $target->getHeight()) / 2));
            }
        }

        if (!$crop && !$border) {
            $img->resample(new Point(), new Point(), $target, $original, $new);
        } elseif ($border) {
            $img->resample($point, new Point(), $target, $original, $new);
        } else {
            $img->resample(new Point(), $point, $target, $original, $new);
        }

        return $img->toString();
    }

    /** Resize SVG image. */
    public function resizeSvg(JobInterface $job): string
    {
        $image = $this->svgImagine->load($job->getRequestImage()->read());
        $target = $job->getTargetDimensions();
        $image->resize(new Box($target->getWidth(), $target->getHeight()));

        return $image->get('svg');
    }
}
