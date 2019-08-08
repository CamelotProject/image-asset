<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Image\Attributes;

use Camelot\ImageAsset\Image\Attributes\Exif;
use PHPExif;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Image\Attributes\Exif
 */
final class ExifTest extends TestCase
{
    public function testCast(): void
    {
        $expected = ['raw' => 'data'];

        $exif = new PHPExif\Exif();
        $exif->setRawData($expected);

        static::assertInstanceOf(Exif::class, Exif::cast($exif));
        static::assertSame($expected, Exif::cast($exif)->getRawData());
    }

    public function providerAspectRatio(): iterable
    {
        yield 'Zeros ' => [0, 0, 0, 0.0];
        yield 'Ones ' => [1, 1, 0, 1.0];
        yield '4x3' => [800, 600, 0, 1.3333333333333333];
        yield '4x3 5' => [800, 600, 5, 0.75];
        yield '4x3 6' => [800, 600, 6, 0.75];
        yield '4x3 7' => [800, 600, 7, 0.75];
        yield '4x3 8' => [800, 600, 8, 0.75];
    }

    /** @dataProvider providerAspectRatio */
    public function testGetAspectRatio(int $width, int $height, int $orientation, float $expected): void
    {
        $exif = new Exif([Exif::WIDTH => $width, Exif::HEIGHT => $height, Exif::ORIENTATION => $orientation]);

        static::assertSame($expected, $exif->getAspectRatio());
    }

    public function testGetLatitude(): void
    {
        $exif = new Exif([Exif::GPS => null]);
        static::assertNull($exif->getLatitude());

        $exif = new Exif([Exif::GPS => '52.071739,4.2398296']);
        static::assertSame(52.071739, $exif->getLatitude());
    }

    public function testGetLongitude(): void
    {
        $exif = new Exif([Exif::GPS => null]);
        static::assertNull($exif->getLongitude());

        $exif = new Exif([Exif::GPS => '52.071739,4.2398296']);
        static::assertSame(4.2398296, $exif->getLongitude());
    }
}
