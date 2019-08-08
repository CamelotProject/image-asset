<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Filesystem;

interface ResolverFilesystemInterface extends FilesystemInterface
{
    public function getBasePaths(): iterable;
}
