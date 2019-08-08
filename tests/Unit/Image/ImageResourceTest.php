<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Image;

use Camelot\ImageAsset\Exception\InvalidArgumentException;
use Camelot\ImageAsset\Exception\UnsupportedFileTypeException;
use Camelot\ImageAsset\Image\Attributes\Color;
use Camelot\ImageAsset\Image\Attributes\Point;
use Camelot\ImageAsset\Image\Dimensions;
use Camelot\ImageAsset\Image\ImageResource;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use Camelot\ImageAsset\Tests\ThumbnailAssertTrait;
use Camelot\Thrower\Thrower;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use function function_exists;
use const PHP_INT_MAX;

/**
 * @covers \Camelot\ImageAsset\Image\ImageResource
 */
final class ImageResourceTest extends TestCase
{
    use ThumbnailAssertTrait;

    public function providerImageNameByType(): iterable
    {
        yield 'GIF' => ['generic/generic-logo.gif'];
        yield 'JPEG' => ['generic/generic-logo.jpg'];
        yield 'PNG' => ['generic/generic-logo.png'];
    }

    public function testConstructInvalidResource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Given resource must be a GD resource');

        new ImageResource(null);
    }

    public function testConstructInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Type or ImageInfo needs to be provided');

        $resource = Thrower::call('imagecreatetruecolor', 128, 128);

        new ImageResource($resource);
    }

    public function testCreateNew(): void
    {
        static::markTestIncomplete();

        ImageResource::createNew();
    }

    /** @dataProvider providerImageNameByType */
    public function testCreateFromString(string $fileName): void
    {
        static::assertInstanceOf(ImageResource::class, $this->getImageResource($fileName));
    }

    public function testCreateFromStringSvg(): void
    {
        static::markTestSkipped();

        static::assertInstanceOf(ImageResource::class, $this->getImageResource('svg.svg'));
    }

    public function testCreateFromStringWebP(): void
    {
        if (!(imagetypes() & IMG_WEBP)) {
            static::markTestSkipped();
        }
        static::assertInstanceOf(ImageResource::class, $this->getImageResource('default.webp'));
    }

    public function testCreateFromStringInvalid(): void
    {
        $this->expectException(UnsupportedFileTypeException::class);

        $this->getImageResource('empty.jpg');
    }

    /** @dataProvider providerImageNameByType */
    public function testCreateFromFile(string $fileName): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        /** @var SplFileInfo $file */
        $file = $filesystem->get($fileName);
        $resource = ImageResource::createFromFile($file->getPathname());

        static::assertInstanceOf(ImageResource::class, $resource);
    }

    public function testCreateFromFileInvalid(): void
    {
        $this->expectException(UnsupportedFileTypeException::class);

        $this->getImageResource('empty.jpg');
    }

    public function testClone(): void
    {
        $resource = $this->getImageResource();

        static::assertNotSame(clone $resource, $resource);
    }

    public function testToString(): void
    {
        static::assertIsString((string) $this->getImageResource());
    }

    public function testGetResource(): void
    {
        static::assertIsResource($this->getImageResource()->getResource());
    }

    public function testGetDimensions(): void
    {
        $dimensions = $this->getImageResource()->getDimensions();

        static::assertSame(1200, $dimensions->getWidth());
        static::assertSame(1200, $dimensions->getHeight());
    }

    public function testGetType(): void
    {
        static::markTestIncomplete();
    }

    public function testGetInfo(): void
    {
        $info = $this->getImageResource()->getInfo();

        static::assertSame('image/jpeg', $info->getMime());
        static::assertTrue($info->isValid());
    }

    public function providerColor(): iterable
    {
        yield [0, 0, 0, null, 0];
        yield [255, 255, 255, 127, 2147483647];
    }

    /** @dataProvider providerColor */
    public function testAllocateColor(int $red, int $green, int $blue, ?int $alpha, int $index): void
    {
        $resource = $this->getImageResource();
        $color = $resource->allocateColor($red, $green, $blue, $alpha);

        static::assertSame($red, $color->getRed());
        static::assertSame($green, $color->getGreen());
        static::assertSame($blue, $color->getBlue());
        static::assertSame($alpha, $color->getAlpha());
        static::assertSame($index, $color->getIndex());
    }

    public function testAllocateTransparentColor(): void
    {
        $resource = $this->getImageResource();
        $color = $resource->allocateTransparentColor();

        static::assertSame(0, $color->getRed());
        static::assertSame(0, $color->getGreen());
        static::assertSame(0, $color->getBlue());
        static::assertSame(127, $color->getAlpha());
        static::assertSame(2130706432, $color->getIndex());
    }

    public function testGetColorAt(): void
    {
        $resource = $this->getImageResource();
        $color = $resource->getColorAt(new Point(42, 24));

        static::assertSame(181, $color->getRed());
        static::assertSame(181, $color->getGreen());
        static::assertSame(181, $color->getBlue());
        static::assertSame(0, $color->getAlpha());
        static::assertSame(11908533, $color->getIndex());
    }

    public function testGetColorAtOutOfBounds(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $resource = $this->getImageResource();
        $resource->getColorAt(new Point(PHP_INT_MAX, PHP_INT_MAX));
    }

    public function testFill(): void
    {
        $color = $this
            ->getImageResource()
            ->fill(new Color(255, 0, 0, 88), new Point(0, 0))
            ->getColorAt(new Point(42, 24))
        ;

        static::assertSame(181, $color->getRed());
        static::assertSame(181, $color->getGreen());
        static::assertSame(181, $color->getBlue());
        static::assertSame(0, $color->getAlpha());
        static::assertSame(11908533, $color->getIndex());
    }

    public function testResample(): void
    {
        $resource = $this->getImageResource();
        $resource->resample(new Point(0, 0), new Point(1024, 1024), new Dimensions(1400, 1400), new Dimensions(1200, 1200));

        static::markTestIncomplete();
    }

    public function providerFlip(): iterable
    {
        yield ['portrait-flip-H.png', 'portrait.png', 'H'];
        yield ['portrait-flip-V.png', 'portrait.png', 'V'];
        yield ['portrait-flip-HV.png', 'portrait.png', 'HV'];
        yield ['landscape-flip-H.png', 'landscape.png', 'H'];
        yield ['landscape-flip-V.png', 'landscape.png', 'V'];
        yield ['landscape-flip-HV.png', 'landscape.png', 'HV'];
    }

    /** @dataProvider providerFlip */
    public function testFlip(string $expected, string $file, string $mode): void
    {
        $resource = $this->getImageResource($file);
        $originalDimensions = $resource->getDimensions();
        $resource->flip($mode);

        $this->assertDimensions($originalDimensions, $resource->getDimensions());
        $this->assertThumbnailsSame(FilesystemMockBuilder::create()->createImages()->read($expected), (string) $resource);
    }

    public function providerRotate(): iterable
    {
        yield ['portrait-rotate-L.png', new Dimensions(640, 427), 'portrait.png', 'L'];
        yield ['portrait-rotate-R.png', new Dimensions(640, 427), 'portrait.png', 'R'];
        yield ['portrait-rotate-T.png', new Dimensions(427, 640), 'portrait.png', 'T'];
        yield ['landscape-rotate-L.png', new Dimensions(667, 1000), 'landscape.png', 'L'];
        yield ['landscape-rotate-R.png', new Dimensions(667, 1000), 'landscape.png', 'R'];
        yield ['landscape-rotate-T.png', new Dimensions(1000, 667), 'landscape.png', 'T'];
    }

    /** @dataProvider providerRotate */
    public function testRotate(string $expectedFilepath, Dimensions $expectedDimensions, string $file, string $angle): void
    {
        $resource = $this->getImageResource($file);

        $resource->rotate($angle);

        $this->assertDimensions($expectedDimensions, $resource->getDimensions());
        $this->assertThumbnailsSame(FilesystemMockBuilder::create()->createImages()->read($expectedFilepath), (string) $resource);
    }

    public function providerFile(): iterable
    {
        yield 'GIF' => ['generic/generic-logo.gif'];
        yield 'JPEG' => ['generic/generic-logo.jpg'];
        yield 'PNG' => ['generic/generic-logo.png'];
        //yield 'WBMP' => ['default.wbmp']; // @FIXME
        if (function_exists('imagecreatefromwebp')) {
            yield 'WEBP' => ['default.webp'];
        }
    }

    /** @dataProvider providerFile */
    public function testToFile(string $file): void
    {
        $this->assertToFile($file);
    }

    public function testToFileUnsupported(): void
    {
        $this->expectException(UnsupportedFileTypeException::class);

        $this->assertToFile('default.png.damaged');
    }

    public function testJpegOrientationNormalized(): void
    {
        $expected = ImageResource::isJpegOrientationNormalized();
        ImageResource::setNormalizeJpegOrientation(!$expected);

        static::assertNotSame($expected, ImageResource::isJpegOrientationNormalized());
    }

    public function providerQuality(): iterable
    {
        yield [-1, null];
        yield [0, 100];
        yield [1, 90];
        yield [2, 80];
        yield [3, 70];
        yield [4, 60];
        yield [5, 50];
        yield [6, 40];
        yield [7, 30];
        yield [8, 20];
        yield [9, 10];
        yield [50, 50];
        yield [100, 100];
        yield [101, null];
    }

    /** @dataProvider providerQuality */
    public function testQuality(int $quality, ?int $expected): void
    {
        if ($expected === null) {
            $this->expectException(InvalidArgumentException::class);
        }

        ImageResource::setQuality($quality);

        static::assertSame($expected, ImageResource::getQuality());
    }

    public function testGetExif(): void
    {
        static::markTestIncomplete();
    }

    private function assertToFile(string $file): void
    {
        $filesystem = FilesystemMockBuilder::create()->createScratch();
        $baseDir = $filesystem->getMountPath();
        $resource = $this->getImageResource($file);

        $filesystem->write("$file.tmp", ''); // Creates the target directory
        $resource->toFile("$baseDir/$file");

        $image = $filesystem->get($file);

        static::assertTrue($image->exists());
    }

    private function getImageResource(string $file = 'placeholder.jpg'): ImageResource
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();

        return ImageResource::createFromFile($filesystem->get($file)->getPathname());
    }
}
