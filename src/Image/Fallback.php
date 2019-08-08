<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Image;

use Camelot\ImageAsset\Filesystem\FilesystemInterface;
use Camelot\ImageAsset\Filesystem\ImageInterface;

/**
 * Lazy loading fallbacks for image requests.
 */
final class Fallback implements FallbackInterface
{
    private FilesystemInterface $filesystem;
    private Dimensions $defaultDimensions;
    private string $defaultImagePath;
    private string $errorImagePath;
    private ?ImageInterface $defaultImage = null;
    private ?ImageInterface $errorImage = null;

    public function __construct(
        FilesystemInterface $filesystem,
        Dimensions $defaultDimensions,
        string $defaultImagePath,
        string $errorImagePath
    ) {
        $this->filesystem = $filesystem;
        $this->defaultDimensions = $defaultDimensions;
        $this->defaultImagePath = $defaultImagePath;
        $this->errorImagePath = $errorImagePath;
    }

    public function getDefaultDimensions(): Dimensions
    {
        return clone $this->defaultDimensions;
    }

    public function getDefaultImage(): ImageInterface
    {
        if ($this->defaultImage === null) {
            /** @var ImageInterface $image */
            $image = $this->filesystem->get($this->defaultImagePath);
            $this->defaultImage = $image;
        }

        return $this->defaultImage;
    }

    public function getErrorImage(): ImageInterface
    {
        if ($this->errorImage === null) {
            /** @var ImageInterface $image */
            $image = $this->filesystem->get($this->errorImagePath);
            $this->errorImage = $image;
        }

        return $this->errorImage;
    }
}
