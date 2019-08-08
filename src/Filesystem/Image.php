<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Filesystem;

use Camelot\ImageAsset\Image\Attributes\Info;

class Image implements ImageInterface
{
    use FileTrait;

    /** @var ?Info */
    private $info = null;

    /** {@inheritdoc} */
    public function getInfo(bool $cache = true): Info
    {
        if (!$cache) {
            $this->info = null;
        }
        $path = $this->getPathname();
        if (!$this->info) {
            $this->info = $path ? Info::createFromFile($path) : Info::createInvalid();
        }

        return $this->info;
    }

    /**
     * {@inheritdoc}
     *
     * Use MIME Type from Info as it has handles SVG detection better.
     */
    public function getMimeType(): ?string
    {
        return $this->getInfo()->getMime();
    }
}
