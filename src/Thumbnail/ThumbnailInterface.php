<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Thumbnail;

use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Image\Dimensions;

interface ThumbnailInterface
{
    public function __toString(): string;

    public function getImage(): ImageInterface;

    public function getThumbnail(): string;

    public function getThumbnailDimensions(): Dimensions;
}
