<?php

declare(strict_types=1);

namespace Camelot\ImageAssets\Tests\Image;

use Camelot\Filesystem\Adapter\Local;
use Camelot\Filesystem\Filesystem;
use Camelot\Filesystem\Handler\Image\Dimensions;
use Camelot\ImageAssets\Image\ImageResource;
use Camelot\ImageAssets\Image\Point;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ImageResourceTest extends TestCase
{
    /** @var Filesystem */
    protected $fs;

    public function setup(): void
    {
        $this->fs = new Filesystem(new Local(__DIR__ . '/images'));
    }

    public function testExifOrientation(): void
    {
        $images = [
            '1-top-left',
            '2-top-right',
            '3-bottom-right',
            '4-bottom-left',
            '5-left-top',
            '6-right-top',
            '7-right-bottom',
            '8-left-bottom',
        ];
        $expected = new Dimensions(400, 200);

        foreach ($images as $name) {
            $image = $this->fs->getImage('exif-orientation/' . $name . '.jpg');
            $resource = ImageResource::createFromString($image->read());

            $this->assertDimensions($expected, $resource->getDimensions());

            $color = $resource->getColorAt(new Point());
            $this->assertTrue(
                $color->getRed() > 250 && $color->getGreen() < 10 && $color->getBlue() < 5,
                'Wrong orientation'
            );
        }
    }

    public function testInvalidImageFromString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid image data');

        ImageResource::createFromString('');
        $this->addToAssertionCount(1);
    }

    protected function assertDimensions(Dimensions $expected, Dimensions $actual): void
    {
        $this->assertEquals($expected, $actual, "Expected dimension $expected does not equal actual $actual");
    }
}
