<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Fixtures\Filesystem;

use Camelot\ImageAsset\Filesystem\FilesystemInterface;
use Camelot\ImageAsset\Image\Dimensions;
use Camelot\ImageAsset\Image\Fallback;

final class FallbackMockBuilder
{
    public static function create(?FilesystemInterface $filesystem = null, ?Dimensions $dimensions = null): Fallback
    {
        return new Fallback(
            $filesystem ?: FilesystemMockBuilder::create()->createImages(),
            $dimensions ?: new Dimensions(256, 128),
            'default.png',
            'error.png'
        );
    }
}
