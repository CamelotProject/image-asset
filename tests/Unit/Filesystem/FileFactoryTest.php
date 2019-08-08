<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Filesystem;

use Camelot\ImageAsset\Exception\IOException;
use Camelot\ImageAsset\Filesystem\FileFactory;
use Camelot\ImageAsset\Filesystem\FileInterface;
use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Filesystem\FileFactory
 */
final class FileFactoryTest extends TestCase
{
    public function providerCreate(): iterable
    {
        yield [ImageInterface::class, 'generic/generic-logo.gif'];
        yield [ImageInterface::class, 'generic/generic-logo.png'];
        yield [ImageInterface::class, 'generic/generic-logo.jpg'];
        yield [ImageInterface::class, 'svg.svg'];
        yield [ImageInterface::class, 'default.webp'];
        yield [FileInterface::class, 'empty.jpg'];
    }

    /** @dataProvider providerCreate */
    public function testCreate(string $expected, string $filePath): void
    {
        $file = FileFactory::create(FilesystemMockBuilder::create()->createImages(), $filePath);

        static::assertInstanceOf($expected, $file);
    }

    public function providerCreateInvalid(): iterable
    {
        yield [IOException::class, 'not-found.gif'];
    }

    /** @dataProvider providerCreateInvalid */
    public function testCreateInvalid(string $expected, string $filePath): void
    {
        $this->expectException($expected);

        FileFactory::create(FilesystemMockBuilder::create()->createImages(), $filePath);
    }
}
