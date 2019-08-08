<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Filesystem;

use Camelot\ImageAsset\Filesystem\Image;
use Camelot\ImageAsset\Image\Attributes\Info;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Filesystem\Image
 */
final class ImageTest extends TestCase
{
    use FileTestTrait;

    public function providerImageFileNames(): iterable
    {
        yield 'PNG' => ['default.png', true, 'image/png'];
        yield 'JPG' => ['placeholder.jpg', true, 'image/jpeg'];
        yield 'SVG' => ['svg.svg', true, 'image/svg+xml'];
        yield 'Not found' => ['nothing.bmp', false, null];
    }

    /** @dataProvider providerImageFileNames */
    public function testGetSize(string $fileName, bool $expected): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $image = new Image($filesystem, $fileName);

        if ($expected) {
            static::assertIsNumeric($image->getSize());
        } else {
            $this->addToAssertionCount(1);
        }
    }

    /** @dataProvider providerImageFileNames */
    public function testGetInfo(string $fileName, bool $exists, ?string $expected): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $image = new Image($filesystem, $fileName);
        $info = $image->getInfo();

        static::assertInstanceOf(Info::class, $info);
        static::assertSame($expected, $info->getMime());
    }

    /** @dataProvider providerImageFileNames */
    public function testGetInfoNoCache(string $fileName, bool $exists, ?string $expected): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $image = new Image($filesystem, $fileName);
        $info = $image->getInfo(false);

        static::assertInstanceOf(Info::class, $info);
        static::assertSame($expected, $info->getMime());
    }

    /** @dataProvider providerImageFileNames */
    public function testGetMimeType(string $fileName, bool $exists, ?string $expected): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $image = new Image($filesystem, $fileName);

        static::assertSame($expected, $image->getMimeType());
    }
}
