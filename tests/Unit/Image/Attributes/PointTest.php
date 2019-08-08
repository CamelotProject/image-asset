<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Image\Attributes;

use Camelot\ImageAsset\Image\Attributes\Point;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Image\Attributes\Point
 */
final class PointTest extends TestCase
{
    public function testGetX(): void
    {
        static::assertSame(42, (new Point(42, 24))->getX());
    }

    public function testSetX(): void
    {
        static::assertSame(42, (new Point())->setX(42)->getX());
    }

    public function testGetY(): void
    {
        static::assertSame(24, (new Point(42, 24))->getY());
    }

    public function testSetY(): void
    {
        static::assertSame(24, (new Point())->setY(24)->getY());
    }

    public function testToString(): void
    {
        static::assertSame('(42, 24)', (string) (new Point(42, 24)));
    }
}
