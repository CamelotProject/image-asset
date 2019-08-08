<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Routing;

interface ThumbnailPathMatcherInterface
{
    public function matchPath(string $filePath, string $action, int $width, int $height): string;

    public function matchAlias(string $filePath, ?string $alias = null): string;
}
