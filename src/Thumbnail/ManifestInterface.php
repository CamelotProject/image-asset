<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Thumbnail;

interface ManifestInterface
{
    public function get(string $filePath): ?string;

    public function set(string $filePath, string $thumbPath): void;
}
