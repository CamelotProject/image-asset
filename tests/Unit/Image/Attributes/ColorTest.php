<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Image\Attributes;

use Camelot\ImageAsset\Exception\InvalidArgumentException;
use Camelot\ImageAsset\Image\Attributes\Color;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Image\Attributes\Color
 */
final class ColorTest extends TestCase
{
    public function testTransparent(): void
    {
        static::assertSame(127, Color::transparent()->getAlpha());

        static::assertSame(0, Color::transparent()->getRed());
        static::assertSame(0, Color::transparent()->getGreen());
        static::assertSame(0, Color::transparent()->getBlue());
        static::assertSame(127, Color::transparent()->getAlpha());
        static::assertNull(Color::transparent()->getIndex());
    }

    public function testWhite(): void
    {
        static::assertSame(255, Color::white()->getRed());
        static::assertSame(255, Color::white()->getGreen());
        static::assertSame(255, Color::white()->getBlue());
        static::assertNull(Color::white()->getAlpha());
        static::assertNull(Color::white()->getIndex());
    }

    public function providerInvalid(): iterable
    {
        yield 'Red out of range negative' => [-1, 0, 0, null, null];
        yield 'Red out of range positive' => [256, 0, 0, null, null];
        yield 'Green out of range negative' => [0, -1, 0, null, null];
        yield 'Green out of range positive' => [0, 256, 0, null, null];
        yield 'Blue out of range negative' => [0, 0, -1, null, null];
        yield 'Blue out of range positive' => [0, 0, 256, null, null];
        yield 'Alpha out of range negative' => [0, 0, 0, -1, null];
        yield 'Alpha out of range positive' => [0, 0, 0, 128, null];
    }

    /** @dataProvider providerInvalid */
    public function testInvalid(int $red, int $green, int $blue, ?int $alpha = null, ?int $index = null): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Color($red, $green, $blue, $alpha, $index);
    }
}
