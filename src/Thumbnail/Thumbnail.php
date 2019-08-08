<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Thumbnail;

use Camelot\ImageAsset\Bridge\Symfony\Mime\MimeTypeGuesserFactory;
use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Image\Dimensions;
use Camelot\ImageAsset\Image\ImageResource;
use Camelot\ImageAsset\Image\Svg;

/**
 * Stores thumbnail data and image file.
 */
final class Thumbnail implements ThumbnailInterface
{
    /** @var ImageInterface */
    private $image;
    /** @var string */
    private $thumbnail;
    /** @var ?Dimensions */
    private $dimensions = null;

    public function __construct(ImageInterface $image, string $thumbnail)
    {
        $this->image = $image;
        $this->thumbnail = $thumbnail;
    }

    public function __toString(): string
    {
        return $this->thumbnail;
    }

    public function getImage(): ImageInterface
    {
        return $this->image;
    }

    public function getThumbnail(): string
    {
        return $this->thumbnail;
    }

    public function getThumbnailDimensions(): Dimensions
    {
        if ($this->dimensions) {
            return $this->dimensions;
        }

        if (MimeTypeGuesserFactory::isSvg($this->thumbnail, null)) {
            $image = Svg::createFromString($this->thumbnail);
            $width = $image->getSize()->getWidth();
            $height = $image->getSize()->getHeight();
        } else {
            $resource = ImageResource::createFromString($this->thumbnail);
            $width = $resource->getDimensions()->getWidth();
            $height = $resource->getDimensions()->getHeight();
        }

        return $this->dimensions = new Dimensions($width, $height);
    }
}
