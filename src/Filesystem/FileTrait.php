<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Filesystem;

use Camelot\ImageAsset\Image\SizeTrait;
use DateTimeImmutable;
use DateTimeInterface;
use SplFileInfo;
use function dirname;
use const DIRECTORY_SEPARATOR;

trait FileTrait
{
    use SizeTrait;

    /** @var FilesystemInterface */
    private $filesystem;
    /** @var SplFileInfo */
    private $fileInfo;
    /** @var string */
    private $relativePath;
    /** @var string */
    private $relativePathname;

    public function __construct(FilesystemInterface $filesystem, string $path)
    {
        $this->filesystem = $filesystem;
        $this->relativePath = dirname($path);
        $this->relativePathname = ltrim($path, DIRECTORY_SEPARATOR);
        $this->fileInfo = new SplFileInfo($this->filesystem->getMountPath() . DIRECTORY_SEPARATOR . ltrim($this->relativePathname, DIRECTORY_SEPARATOR));
    }

    public function __toString(): string
    {
        return $this->getPathname();
    }

    public function exists(): bool
    {
        return $this->filesystem->exists($this->getRelativePathname());
    }

    public function read(): string
    {
        return $this->filesystem->read($this->getRelativePathname());
    }

    public function write(string $content): void
    {
        $this->filesystem->write($this->getRelativePathname(), $content);
    }

    public function rename(string $newPath): void
    {
        $this->filesystem->rename($this->getRelativePathname(), $newPath);
    }

    public function getPath(): string
    {
        return $this->fileInfo->getPath();
    }

    public function getPathname(): string
    {
        return $this->fileInfo->getPathname();
    }

    public function getFilename(): string
    {
        return $this->fileInfo->getFilename();
    }

    public function getExtension(): string
    {
        return $this->fileInfo->getExtension();
    }

    /**
     * Returns the relative path.
     *
     * This path does not contain the file name.
     *
     * @return string the relative path
     */
    public function getRelativePath(): string
    {
        return $this->relativePath;
    }

    /**
     * Returns the relative path name.
     *
     * This path contains the file name.
     *
     * @return string the relative path name
     */
    public function getRelativePathname(): string
    {
        return $this->relativePathname;
    }

    public function getFilenameWithoutExtension(): string
    {
        $filename = $this->getFilename();

        return pathinfo($filename, PATHINFO_FILENAME);
    }

    public function getSize(): int
    {
        return (int) $this->fileInfo->getSize();
    }

    public function getMDateTime(): DateTimeInterface
    {
        return DateTimeImmutable::createFromFormat('U', (string) $this->getMTime());
    }

    public function getMTime(): int
    {
        return (int) $this->fileInfo->getMTime();
    }
}
