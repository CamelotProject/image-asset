<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Filesystem;

use Camelot\ImageAsset\Exception\BadMethodCallException;
use Camelot\ImageAsset\Exception\IOException;
use Camelot\ImageAsset\Exception\ResolverIOException;
use Camelot\ImageAsset\Exception\RuntimeException;
use Camelot\ImageAsset\Exception\UnsupportedFileTypeException;
use Webmozart\Assert\Assert;

final class ResolverFilesystem implements ResolverFilesystemInterface
{
    /** @var FilesystemInterface[] */
    private $filesystems;

    public function __construct(iterable $filesystems)
    {
        foreach ($filesystems as $filesystem) {
            Assert::isInstanceOf($filesystem, FilesystemInterface::class);
            $this->filesystems[] = $filesystem;
        }
    }

    /** @codeCoverageIgnore */
    public function finder(): FinderInterface
    {
        // @TODO
        throw new RuntimeException(sprintf('Cannot use finder on a %s', ResolverFilesystemInterface::class));
    }

    public function create(string $filename): FileInterface
    {
        return new File($this->filesystems[0], $filename); // @TODO
    }

    public function getMountPath(): string
    {
        throw new BadMethodCallException(sprintf('Can not call %s on %s', __FUNCTION__, __CLASS__));
    }

    public function getBasePaths(): iterable
    {
        $paths = [];
        foreach ($this->filesystems as $filesystem) {
            $paths[] = $filesystem->getMountPath();
        }

        return $paths;
    }

    public function exists($files): bool
    {
        foreach ($this->filesystems as $filesystem) {
            if ($filesystem->exists($files)) {
                return true;
            }
        }

        return false;
    }

    public function get(string $filename): FileInterface
    {
        return $this->doOperation(__FUNCTION__, $filename);
    }

    public function read(string $filename): string
    {
        return $this->doOperation(__FUNCTION__, $filename);
    }

    public function write(string $filename, $content): FileInterface
    {
        return $this->doOperation(__FUNCTION__, $filename, $content);
    }

    public function rename(string $origin, string $target, bool $overwrite = false): void
    {
        $this->doOperation(__FUNCTION__, $origin, $target, $overwrite);
    }

    private function doOperation(string $operation, ...$args)
    {
        $exceptions = [];
        foreach ($this->filesystems as $filesystem) {
            try {
                return $filesystem->$operation(...$args);
            } catch (IOException | UnsupportedFileTypeException $e) {
                $exceptions[] = $e;

                continue;
            }
        }
        $trace = debug_backtrace(~DEBUG_BACKTRACE_PROVIDE_OBJECT | ~DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        throw new ResolverIOException($trace[1]['function'], $exceptions);
    }
}
