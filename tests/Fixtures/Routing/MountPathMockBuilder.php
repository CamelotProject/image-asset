<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Fixtures\Routing;

final class MountPathMockBuilder
{
    public static function buildPath(string $path)
    {
        return $_SERVER['APP_MOUNT_POINT'] . '/' . ltrim($path, '/');
    }

    public function __toString(): string
    {
        return (string) $_SERVER['APP_MOUNT_POINT'];
    }
}
