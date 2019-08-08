<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Filesystem;

use Camelot\ImageAsset\Exception\IOException;
use Camelot\ImageAsset\Exception\UnsupportedFileTypeException;

interface FilesystemInterface
{
    public function finder(): FinderInterface;

    /**
     * Create an uncommitted file.
     *
     * NOTE: This file will not exist until saved.
     */
    public function create(string $filename): FileInterface;

    /** Checks the existence of files or directories. */
    public function exists(string $filename): bool;

    /**
     * Gets the file as a \Camelot\ImageAsset\Filesystem\FileInterface object.
     *
     * @throws IOException                  if the file cannot be read
     * @throws UnsupportedFileTypeException if the file MIME type is not supported or known
     */
    public function get(string $filename): FileInterface;

    /**
     * Reads the contents of a file.
     *
     * @throws IOException if the file cannot be read
     */
    public function read(string $filename): string;

    /**
     * Renames a file or a directory.
     *
     * @throws IOException When target file or directory already exists
     * @throws IOException When origin cannot be renamed
     */
    public function rename(string $origin, string $target, bool $overwrite = false): void;

    /**
     * Write content into a file.
     *
     * @param mixed $content
     *
     * @throws IOException if the file cannot be written to
     */
    public function write(string $filename, $content): FileInterface;

    /** Gets the mount point of the filesystem on the OS root. */
    public function getMountPath(): string;
}
