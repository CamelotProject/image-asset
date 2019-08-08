<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Util;

use Camelot\ImageAsset\Util\Url;
use PHPUnit\Framework\TestCase;

final class UrlTest extends TestCase
{
    public function providerPackagePath(): iterable
    {
        yield 'Base directory, no package name' => ['image.png', '', 'image.png'];
        yield 'Base directory, with package name' => ['image.png', 'images', 'image.png'];
        yield 'Subdirectory, no package name' => ['images/image.png', '', 'images/image.png'];
        yield 'Subdirectory, with package name' => ['images/image.png', 'images', 'image.png'];
        yield 'Deep subdirectory, with package name' => ['images/gallery/image.png', 'images', 'gallery/image.png'];
    }

    /** @dataProvider providerPackagePath */
    public function testGetPackagePath(string $path, ?string $packageName, string $expected): void
    {
        static::assertSame($expected, Url::getPackagePath($path, $packageName));
    }
}
