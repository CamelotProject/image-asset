<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Util;

final class Url
{
    /** @codeCoverageIgnore */
    private function __construct()
    {
    }

    public static function getPackagePath(string $path, ?string $packageName): string
    {
        if ($packageName) {
            $path = preg_replace("#^(/|)({$packageName}/)#", '', $path);
        }

        return $path;
    }
}
