<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Bridge\Symfony\Mime;

use Camelot\ImageAsset\Bridge\Symfony\Mime\MimeTypeGuesserFactory;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\MimeTypes;

/**
 * @covers \Camelot\ImageAsset\Bridge\Symfony\Mime\MimeTypeGuesserFactory
 */
final class MimeTypeGuesserFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        static::assertInstanceOf(MimeTypes::class, MimeTypeGuesserFactory::create());
    }

    public function providerSvg(): iterable
    {
        yield [true, 'svg.svg'];
        yield [true, 'svg-undeclared.svg'];
        yield [false, 'default.png'];
        yield [false, 'error.png'];
    }

    /** @dataProvider providerSvg */
    public function testIsSvg(bool $expected, string $filePath): void
    {
        $file = FilesystemMockBuilder::create()->createImages()->get($filePath);
        static::assertSame($expected, MimeTypeGuesserFactory::isSvg($file->read(), null));
    }
}
