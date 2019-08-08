<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Image;

use Camelot\ImageAsset\Image\Dimensions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Image\Dimensions
 */
final class DimensionsTest extends TestCase
{
    public function testToString(): void
    {
        static::assertSame('0 × 0 px', (string) new Dimensions());
        static::assertSame('1024 × 768 px', (string) new Dimensions(1024, 768));
    }

    public function testWidth(): void
    {
        static::assertSame(1024, (new Dimensions())->setWidth(1024)->getWidth());
    }

    public function testHeight(): void
    {
        static::assertSame(768, (new Dimensions())->setHeight(768)->getHeight());
    }
}
