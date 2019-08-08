<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Bridge\Twig\Extension;

use Camelot\ImageAsset\Bridge\Twig\Extension\ImageAssetExtension;
use Camelot\ImageAsset\Routing\ThumbnailPathMatcherInterface;
use Camelot\ImageAsset\Tests\Fixtures\Routing\MountPathMockBuilder;
use PHPUnit\Framework\TestCase;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * @covers \Camelot\ImageAsset\Bridge\Twig\Extension\ImageAssetExtension
 */
final class ImageAssetExtensionTest extends TestCase
{
    /** @var ThumbnailPathMatcherInterface */
    private $pathMatcher;

    protected function setUp(): void
    {
        $this->pathMatcher = $this->createMock(ThumbnailPathMatcherInterface::class);
    }

    public function testGetFilters(): void
    {
        $filters = $this->getImageAssetExtension()->getFilters();
        /** @var TwigFilter $filter */
        $filter = $filters[0];

        static::assertInstanceOf(TwigFilter::class, $filter);
        static::assertSame('thumbnail', $filter->getName());
    }

    public function testGetFunctions(): void
    {
        $filters = $this->getImageAssetExtension()->getFunctions();
        /** @var TwigFunction $filter */
        $filter = $filters[0];

        static::assertInstanceOf(TwigFunction::class, $filter);
        static::assertSame('thumbnail', $filter->getName());
    }

    public function testGetAssetThumbnail(): void
    {
        $this->pathMatcher
            ->expects(static::once())
            ->method('matchAlias')
            ->with('placeholder.jpg', 'test_128x128')
            ->willReturn(MountPathMockBuilder::buildPath('placeholder.jpg'))
        ;
        static::assertSame(MountPathMockBuilder::buildPath('placeholder.jpg'), $this->getImageAssetExtension()->getAssetThumbnail('placeholder.jpg', 'test_128x128'));
    }

    public function testGetAssetThumbnailEmptyAlias(): void
    {
        $this->pathMatcher
            ->expects(static::once())
            ->method('matchAlias')
            ->with('placeholder.jpg', null)
            ->willReturn(MountPathMockBuilder::buildPath('placeholder.jpg'))
        ;
        static::assertSame(MountPathMockBuilder::buildPath('placeholder.jpg'), $this->getImageAssetExtension()->getAssetThumbnail('placeholder.jpg'));
    }

    private function getImageAssetExtension(ThumbnailPathMatcherInterface $pathMatcher = null): ImageAssetExtension
    {
        return new ImageAssetExtension($pathMatcher ?: $this->pathMatcher);
    }
}
