<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Image;

use Camelot\ImageAsset\Filesystem\ImageInterface;

interface FallbackInterface
{
    public function getDefaultDimensions(): Dimensions;

    public function getDefaultImage(): ImageInterface;

    public function getErrorImage(): ImageInterface;
}
