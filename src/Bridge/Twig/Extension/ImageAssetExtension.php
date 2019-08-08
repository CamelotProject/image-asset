<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Bridge\Twig\Extension;

use Camelot\ImageAsset\Routing\ThumbnailPathMatcherInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class ImageAssetExtension extends AbstractExtension
{
    /** @var ThumbnailPathMatcherInterface */
    private $pathMatcher;

    public function __construct(ThumbnailPathMatcherInterface $pathMatcher)
    {
        $this->pathMatcher = $pathMatcher;
    }

    /** @return TwigFilter[] */
    public function getFilters(): array
    {
        return [
            new TwigFilter('thumbnail', [$this, 'getAssetThumbnail']),
        ];
    }

    /** @return TwigFunction[] */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('thumbnail', [$this, 'getAssetThumbnail']),
        ];
    }

    public function getAssetThumbnail(string $filePath, string $alias = null): string
    {
        return $this->pathMatcher->matchAlias($filePath, $alias);
    }
}
