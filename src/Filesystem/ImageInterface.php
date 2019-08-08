<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Filesystem;

use Camelot\ImageAsset\Image\Attributes\Info;

/**
 * This represents an image file.
 */
interface ImageInterface extends FileInterface
{
    /** Get the file's MIME Type. */
    public function getMimeType(): ?string;

    /** Returns the info for this image. */
    public function getInfo(): Info;
}
