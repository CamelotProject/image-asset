<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Filesystem;

use Camelot\ImageAsset\Filesystem\File;
use Camelot\ImageAsset\Filesystem\Image;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount;
use PHPUnit\Framework\MockObject\Matcher\InvokedCount;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @method static void assertSame($expected, $actual, string $message = '')
 * @method static void assertTrue($condition, string $message = '')
 * @method static void assertFalse($condition, string $message = '')
 * @method static void assertIsString($condition, string $message = '')
 * @method static void assertInstanceOf(string $expected, $actual, string $message = '')
 * @method void            expectException(string $exception)
 * @method void            expectExceptionMessage(string $message)
 * @method void            expectExceptionMessageRegExp(string $messageRegExp)
 * @method MockObject      createMock($originalClassName)
 * @method AnyInvokedCount any()
 * @method InvokedCount    once()
 */
trait FileTestTrait
{
    public function providerImageFileNames(): iterable
    {
        yield 'PNG' => ['default.png', true, 'image/png'];
        yield 'JPG' => ['placeholder.jpg', true, 'image/jpeg'];
        yield 'SVG' => ['svg.svg', true, 'image/svg+xml'];
        yield 'Not found' => ['nothing.bmp', false, null];
    }

    /** @dataProvider providerImageFileNames */
    public function testExists(string $fileName, bool $expected): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $image = new Image($filesystem, $fileName);

        self::assertSame($expected, $image->exists());
    }

    /** @dataProvider providerImageFileNames */
    public function testRead(string $fileName, bool $expected): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $image = new Image($filesystem, $fileName);

        if ($expected) {
            self::assertIsString($image->read());
        } else {
            $this->addToAssertionCount(1);
        }
    }

    public function testWrite(): void
    {
        $builder = FilesystemMockBuilder::create();
        $imagesFilesystem = $builder->createImages();
        $scratchFilesystem = $builder->createScratch();
        $scratchPath = $scratchFilesystem->getMountPath();
        $expected = $imagesFilesystem->read('default.png');

        $image = new Image($scratchFilesystem, 'test.png');
        $image->write($expected);

        self::assertFileExists("$scratchPath/test.png");
        self::assertStringEqualsFile("$scratchPath/test.png", $expected);
    }

    /**
     * @depends testWrite
     */
    public function testRename(): void
    {
        $builder = FilesystemMockBuilder::create();
        $imagesFilesystem = $builder->createImages();
        $scratchFilesystem = $builder->createScratch();
        $scratchPath = $scratchFilesystem->getMountPath();
        $expected = $imagesFilesystem->read('default.png');

        $image = new Image($scratchFilesystem, 'test.png');
        $image->write($expected);
        $image->rename('result.png');

        self::assertFileNotExists("$scratchPath/test.png");
        self::assertFileExists("$scratchPath/result.png");
        self::assertStringEqualsFile("$scratchPath/result.png", $expected);
    }

    /** @dataProvider providerImageFileNames */
    public function testGetMDateTime(string $fileName, bool $expected): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $image = new Image($filesystem, $fileName);

        if ($expected) {
            self::assertInstanceOf(DateTimeInterface::class, $image->getMDateTime());
        } else {
            $this->addToAssertionCount(1);
        }
    }

    /** @dataProvider providerImageFileNames */
    public function testGetMimeType(string $fileName, bool $exists, ?string $expected): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $image = new Image($filesystem, $fileName);

        self::assertSame($expected, $image->getMimeType());
    }

    public function testGetPath(): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $image = $filesystem->get('default.png');

        self::assertRegExp('#tests.Fixtures.App.public.images$#', $image->getPath());
    }

    public function testGetPathname(): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $image = $filesystem->get('default.png');

        self::assertRegExp('#tests.Fixtures.App.public.images.default.png$#', $image->getPathname());
    }

    public function testGetFilename(): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $image = $filesystem->get('default.png');

        self::assertSame('default.png', $image->getFilename());
    }

    public function testGetFilenameWithoutExtension(): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $image = $filesystem->get('default.png');

        self::assertSame('default', $image->getFilenameWithoutExtension());
    }

    public function testGetExtension(): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $image = $filesystem->get('default.png');

        self::assertSame('png', $image->getExtension());
    }

    public function testGetRelativePath(): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $image = $filesystem->get('generic/generic-logo.png');

        self::assertSame('generic', $image->getRelativePath());
    }

    public function testGetRelativePathname(): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $image = $filesystem->get('generic/generic-logo.png');

        self::assertSame('generic/generic-logo.png', $image->getRelativePathname());
    }

    public function testGetSize(): void
    {
        self::assertSame(3484, (new File(FilesystemMockBuilder::create()->createImages(), 'default.png'))->getSize());
    }

    public function testGetMTime(): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $image = $filesystem->get('generic/generic-logo.png');

        self::assertGreaterThan((new DateTime('Jan 1 2019'))->getTimestamp(), $image->getMTime());
    }
}
