<?php

declare(strict_types=1);

namespace Camelot\ImageAssets;

use Camelot\Filesystem\Handler\Image;

/**
 * @author Carson Full <carsonfull@gmail.com>
 */
interface FinderInterface
{
    /**
     * Finds the image based on the given path.
     *
     * @param string $path
     *
     * @return Image
     */
    public function find($path);
}
