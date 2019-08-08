<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Bridge\Symfony\Mime;

use Camelot\ImageAsset\Bridge\Symfony\Mime\MimeTypeGuesserFactory;
use Camelot\ImageAsset\Filesystem\Image;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Bridge\Symfony\Mime\BinaryMimeTypeGuesser
 */
final class BinaryMimeTypeGuesserTest extends TestCase
{
    public function providerMimeType(): iterable
    {
        yield 'asset.jpg' => ['asset.jpg', 'image/jpeg'];
        yield 'default.png' => ['default.png', 'image/png'];
        yield 'default.webp' => ['default.webp', 'image/webp'];
        yield 'generic/generic-logo.gif' => ['generic/generic-logo.gif', 'image/gif'];
        yield 'generic/generic-logo.jpg' => ['generic/generic-logo.jpg', 'image/jpeg'];
        yield 'generic/generic-logo.png' => ['generic/generic-logo.png', 'image/png'];
        yield 'landscape.png' => ['landscape.png', 'image/png'];
        yield 'placeholder-128x128b.jpg' => ['placeholder-128x128b.jpg', 'image/jpeg'];
        yield 'placeholder.jpg' => ['placeholder.jpg', 'image/jpeg'];
        yield 'portrait.png' => ['portrait.png', 'image/png'];
        yield 'reverse.jpg' => ['reverse.jpg', 'image/jpeg'];
        yield 'svg.svg' => ['svg.svg', 'image/svg+xml'];
        yield 'svg-undeclared.svg' => ['svg-undeclared.svg', 'image/svg+xml'];
    }

    /** @dataProvider providerMimeType */
    public function testGuessMimeTypeFileName(string $file, string $expected): void
    {
        $guesser = MimeTypeGuesserFactory::create();
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $file = $filesystem->get($file);

        /* @var Image $file */
        static::assertSame($expected, $guesser->guessMimeType($file->getPathname()));
    }

    /** @dataProvider providerMimeType */
    public function testGuessMimeTypeFileContent(string $file, string $expected): void
    {
        $guesser = MimeTypeGuesserFactory::create();
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $data = $filesystem->read($file);

        static::assertSame($expected, $guesser->guessMimeType($data));
    }
}
