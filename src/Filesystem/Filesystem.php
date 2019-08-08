<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Filesystem;

use Camelot\ImageAsset\Exception\IOException;
use Camelot\Thrower\Thrower;
use ErrorException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use const DIRECTORY_SEPARATOR;

final class Filesystem implements FilesystemInterface
{
    /** @var string */
    private $mountPath;
    /** @var FinderInterface */
    private $finder;
    /** @var SymfonyFilesystem */
    private $decorated;

    public function __construct(string $mountPath, SymfonyFilesystem $decorated = null)
    {
        $this->decorated = $decorated ?: new SymfonyFilesystem();
        $this->mountPath = $mountPath;
        $this->finder = new Finder($this);
    }

    public function finder(): FinderInterface
    {
        return $this->finder;
    }

    public function create(string $filename): FileInterface
    {
        return new File($this, $filename);
    }

    public function get(string $filename): FileInterface
    {
        return FileFactory::create($this, $filename);
    }

    public function exists(string $filename): bool
    {
        return $this->decorated->exists((string) $this->resolve($filename));
    }

    public function read(string $filename): string
    {
        $file = $this->resolve($filename);

        try {
            $contents = Thrower::call('file_get_contents', (string) $file);
        } catch (ErrorException $e) {
            throw new IOException(sprintf('Failed to read file "%s" on filesystem base "%s".', $file->getRelativePathname(), $this->mountPath), (string) $file); // @codeCoverageIgnore
        }

        return $contents;
    }

    public function rename(string $origin, string $target, bool $overwrite = false): void
    {
        $file = $this->resolve($origin);

        try {
            $this->decorated->rename((string) $file, $this->resolve($target), $overwrite);
        } catch (\Symfony\Component\Filesystem\Exception\IOException $e) {
            throw new IOException(sprintf('Failed to rename file "%s"' . PHP_EOL . 'Filesystem base: "%s".' . PHP_EOL . 'Reason: %s".', $file->getRelativePathname(), $this->mountPath, $e->getMessage()), (string) $file, 0, $e);
        }
    }

    public function write(string $filename, $content): FileInterface
    {
        $file = $this->resolve($filename);

        try {
            $this->decorated->dumpFile((string)$file, $content);
        } catch (\Symfony\Component\Filesystem\Exception\IOException $e) {
            throw new IOException(sprintf('Failed to write to file "%s"' . PHP_EOL . 'Filesystem base: "%s".' . PHP_EOL . 'Reason: %s".', $file->getRelativePathname(), $this->mountPath, $e->getMessage()), (string) $file, 0, $e);
        }

        return FileFactory::create($this, $filename);
    }

    public function getMountPath(): string
    {
        return $this->mountPath;
    }

    private function resolve(string $filename): FileInterface
    {
        $filename = ltrim($filename, '/');
        $relativePathname = rtrim(ltrim($filename, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);

        return new Image($this, $relativePathname);
    }
}
