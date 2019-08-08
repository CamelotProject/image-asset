<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Filesystem;

use Camelot\ImageAsset\Exception\BadMethodCallException;
use Camelot\ImageAsset\Image\Type\Type;
use Iterator;
use Symfony\Component\Finder\Finder as SymfonyFinder;
use Webmozart\PathUtil\Path;

final class Finder implements FinderInterface
{
    /** @var FilesystemInterface */
    private $filesystem;
    /** @var array */
    private $names = [];
    /** @var array */
    private $notNames = [];
    /** @var array */
    private $dirs = [];
    /** @var array */
    private $exclude = [];
    /** @var array */
    private $depths = [];

    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function in(array $dirs): FinderInterface
    {
        $this->dirs = array_merge($this->dirs, $dirs);

        return $this;
    }

    public function name(array $patterns): FinderInterface
    {
        $this->names = array_merge($this->names, $patterns);

        return $this;
    }

    public function notName(array $patterns): FinderInterface
    {
        $this->notNames = array_merge($this->notNames, $patterns);

        return $this;
    }

    public function count(): int
    {
        return iterator_count($this->getIterator());
    }

    public function exclude(array $dirs): FinderInterface
    {
        $this->exclude = array_merge($this->exclude, $dirs);

        return $this;
    }

    public function depth(array $levels): FinderInterface
    {
        $this->depths = array_merge($this->depths, $levels);

        return $this;
    }

    /**
     * Check if the any results were found.
     */
    public function hasResults(): bool
    {
        foreach ($this->getIterator() as $_) {
            return true;
        }

        return false;
    }

    public function getIterator(): Iterator
    {
        return $this->doFind();
    }

    private function doFind(): Iterator
    {
        $finder = new SymfonyFinder();
        $finder
            ->files()
            ->in($this->resolve($this->dirs) ?: $this->filesystem->getMountPath())
            ->name($this->names ?: '/\.(' . implode('|', Type::getExtensions()) . ')$/')
            ->notName($this->notNames)
        ;
        foreach ($finder as $file) {
            yield FileFactory::create($this->filesystem, Path::makeRelative((string) $file, $this->filesystem->getMountPath()));
        }
    }

    private function resolve(array $paths): array
    {
        $result = [];
        foreach ($paths as $path) {
            if (Path::isAbsolute($path)) {
                throw new BadMethodCallException(sprintf('Can not add absolute path "%s" to %s::in()', $path, __CLASS__));
            }
            $result[] = Path::makeAbsolute($path, $this->filesystem->getMountPath());
        }

        return $result;
    }
}
