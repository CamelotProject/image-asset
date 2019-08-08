<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Thumbnail;

use Camelot\ImageAsset\Exception\InvalidArgumentException;
use Psr\Cache\CacheItemPoolInterface;
use Webmozart\PathUtil\Path;

final class Manifest implements ManifestInterface
{
    /** @var CacheItemPoolInterface */
    private $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public function get(string $filePath): ?string
    {
        if (!Path::getExtension($filePath)) {
            throw new InvalidArgumentException(sprintf('Invalid file requested: %s', $filePath));
        }

        $key = base64_encode(__CLASS__ . $filePath);
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return $item->get();
        }

        return null;
    }

    public function set(string $filePath, string $thumbPath): void
    {
        if (!Path::getExtension($filePath)) {
            throw new InvalidArgumentException(sprintf('Invalid file requested: %s', $filePath));
        }

        $key = base64_encode(__CLASS__ . $filePath);
        $item = $this->cache->getItem($key);
        $item->set($thumbPath);

        $this->cache->save($item);
    }
}
