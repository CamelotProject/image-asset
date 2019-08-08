<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Routing;

use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Routing\ThumbnailPathMatcher;
use Camelot\ImageAsset\Routing\ThumbnailPathMatcherInterface;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FallbackMockBuilder;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use Camelot\ImageAsset\Tests\Fixtures\Image\Attributes\AliasesMockBuilder;
use Camelot\ImageAsset\Tests\Fixtures\Routing\MountPathMockBuilder;
use Camelot\ImageAsset\Tests\Fixtures\Routing\UrlGeneratorMockBuilder;
use Camelot\ImageAsset\Tests\Fixtures\Transaction\TransactionMockBuilder;
use Camelot\ImageAsset\Thumbnail\Manifest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * @covers \Camelot\ImageAsset\Routing\ThumbnailPathMatcher
 */
final class ThumbnailPathMatcherTest extends TestCase
{
    public function providerPaths(): iterable
    {
        yield [MountPathMockBuilder::buildPath('100x200/crop/placeholder.jpg'), 'placeholder.jpg', 'crop', 100, 200];
        yield [MountPathMockBuilder::buildPath('128x128/border/placeholder.jpg'), 'placeholder.jpg', 'border', 128, 128];
        yield [MountPathMockBuilder::buildPath('800x600/fit/placeholder.jpg'), 'placeholder.jpg', 'fit', 800, 600];
        yield [MountPathMockBuilder::buildPath('1600x900/resize/placeholder.jpg'), 'placeholder.jpg', 'resize', 1600, 900];

        yield [MountPathMockBuilder::buildPath('100x200/crop/placeholder.jpg'), 'placeholder.jpg', 'c', 100, 200];
        yield [MountPathMockBuilder::buildPath('128x128/border/placeholder.jpg'), 'placeholder.jpg', 'b', 128, 128];
        yield [MountPathMockBuilder::buildPath('800x600/fit/placeholder.jpg'), 'placeholder.jpg', 'f', 800, 600];
        yield [MountPathMockBuilder::buildPath('1600x900/resize/placeholder.jpg'), 'placeholder.jpg', 'r', 1600, 900];
    }

    /** @dataProvider providerPaths */
    public function testMatchPath(string $expected, string $filePath, string $action, int $width, int $height): void
    {
        $thumbnailPathMatcher = $this->getThumbnailPathMatcher();

        static::assertSame($expected, $thumbnailPathMatcher->matchPath($filePath, $action, $width, $height));
    }

    public function testMatchCachedPath(): void
    {
        $manifest = new Manifest(new ArrayAdapter());
        $manifest->set('file/path.ext', 'thumb/path.ext');
        $thumbnailPathMatcher = $this->getThumbnailPathMatcher($manifest);

        static::assertSame('thumb/path.ext', $thumbnailPathMatcher->matchPath('file/path.ext', Action::CROP, 128, 128));
    }

    public function providerAliasPaths(): iterable
    {
        yield [MountPathMockBuilder::buildPath('100x200/crop/placeholder.jpg'), 'placeholder.jpg', 'test_100x200'];
        yield [MountPathMockBuilder::buildPath('128x128/border/placeholder.jpg'), 'placeholder.jpg', 'test_128x128'];
        yield [MountPathMockBuilder::buildPath('800x600/fit/placeholder.jpg'), 'placeholder.jpg', 'test_800x600'];
        yield [MountPathMockBuilder::buildPath('1600x900/resize/placeholder.jpg'), 'placeholder.jpg', 'test_1600x900'];
        yield [MountPathMockBuilder::buildPath('256x128/crop/placeholder.jpg'), 'placeholder.jpg', null];
    }

    /** @dataProvider providerAliasPaths */
    public function testMatchAlias(string $expected, string $filePath, ?string $alias): void
    {
        $thumbnailPathMatcher = $this->getThumbnailPathMatcher();

        static::assertSame($expected, $thumbnailPathMatcher->matchAlias($filePath, $alias));
    }

    private function getThumbnailPathMatcher(Manifest $manifest = null): ThumbnailPathMatcherInterface
    {
        return new ThumbnailPathMatcher(
            $manifest ?: new Manifest(new ArrayAdapter()),
            AliasesMockBuilder::create(),
            FallbackMockBuilder::create(),
            FilesystemMockBuilder::create()->createImages(),
            TransactionMockBuilder::createBuilder(),
            TransactionMockBuilder::createProcessor(),
            UrlGeneratorMockBuilder::create(),
        );
    }
}
