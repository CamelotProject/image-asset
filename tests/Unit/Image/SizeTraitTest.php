<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Image;

use Camelot\ImageAsset\Image\SizeTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Image\SizeTrait
 */
final class SizeTraitTest extends TestCase
{
    public function providerSizeFormatted(): iterable
    {
        yield [1, true, '1 B'];
        yield [1024, false, '1024 B'];
        yield [1024, true, '1.0 KB'];
        yield [1048576, false, '1024.00 KiB'];
        yield [1048576, true, '1.0 MB'];
        yield [1073741824, false, '1024.00 MiB'];
        yield [1073741824, true, '1073.7 MB'];
    }

    /** @dataProvider providerSizeFormatted */
    public function testGetSizeFormatted(int $size, bool $si, string $expected): void
    {
        static::assertSame($expected, $this->getSizeTrait($size)->getSizeFormatted($si));
    }

    public function testGetSize(): void
    {
        static::assertSame(1024, $this->getSizeTrait(1024)->getSize());
    }

    private function getSizeTrait(int $size): object
    {
        return new class($size) {
            use SizeTrait;

            /** @var int */
            private $size;

            public function __construct(int $size)
            {
                $this->size = $size;
            }

            public function getSize(): int
            {
                return $this->size;
            }
        };
    }
}
