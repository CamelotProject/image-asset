<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Fixtures\Thumbnail;

use Camelot\ImageAsset\Thumbnail\NameGenerator;

final class ThumbnailMockBuilder
{
    public static function createNameGenerator(): NameGenerator
    {
        return new NameGenerator('{width}x{height}/{action}/{file}');
    }
}
