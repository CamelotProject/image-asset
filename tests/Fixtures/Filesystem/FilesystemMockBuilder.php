<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Fixtures\Filesystem;

use Camelot\ImageAsset\Filesystem\Filesystem;
use Camelot\ImageAsset\Util\Uuid;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

final class FilesystemMockBuilder
{
    /** @var ?string */
    private $public;
    /** @var ?string */
    private $imagesDir;
    /** @var ?string */
    private $thumbsDir;
    /** @var ?string */
    private $tempDir;

    public function __construct(?string $public = null, ?string $imagesDir = null, ?string $thumbsDir = null, ?string $tempDir = null)
    {
        $this->public = $public ?: realpath(__DIR__ . '/../App/public');
        $this->imagesDir = $imagesDir ?: $this->public . '/images';
        $this->thumbsDir = $thumbsDir ?: static::ensureCleanPath(($_SERVER['APP_SAVE_PATH'] ?? null) ?: $this->public . '/thumbs');
        $this->tempDir = $tempDir ?: static::ensureCleanPath(__DIR__ . '/../App/var/scratch/' . Uuid::uuid4());
    }

    public function __destruct()
    {
        $fs = new SymfonyFilesystem();
        if ($this->thumbsDir && $fs->exists($this->thumbsDir)) {
            $fs->remove($this->thumbsDir);
        }
        if ($this->tempDir && $fs->exists($this->tempDir)) {
            $fs->remove($this->tempDir);
        }
    }

    public static function create(?string $public = null, ?string $imagesDir = null, ?string $thumbsDir = null, ?string $tempDir = null): self
    {
        return new self($public, $imagesDir, $thumbsDir, $tempDir);
    }

    public function createPublic(): Filesystem
    {
        return new Filesystem($this->public);
    }

    public function createImages(): Filesystem
    {
        return new Filesystem($this->imagesDir);
    }

    public function createThumbs(): Filesystem
    {
        return new Filesystem($this->thumbsDir);
    }

    public function createScratch(): Filesystem
    {
        return new Filesystem($this->tempDir);
    }

    private static function ensureCleanPath(string $path): string
    {
        $fs = new SymfonyFilesystem();
        if ($fs->exists($path)) {
            $fs->remove($path);
        }
        $fs->mkdir($path);

        return realpath($path);
    }
}
