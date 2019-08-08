<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Filesystem;

/**
 * This represents a filesystem file.
 */
interface FileInterface
{
    public function __toString(): string;

    /** Test if the file exists. */
    public function exists(): bool;

    /** Read the file. */
    public function read(): string;

    /** Write TO the new file. */
    public function write(string $content): void;

    /** Rename the file. */
    public function rename(string $newPath): void;

    /** Gets the path without filename. */
    public function getPath(): string;

    /** Gets the path to the file. */
    public function getPathname(): string;

    /**
     * Returns the relative path.
     *
     * This path does not contain the file name.
     */
    public function getRelativePath(): string;

    /**
     * Returns the relative path name.
     *
     * This path contains the file name.
     */
    public function getRelativePathname(): string;

    /** Gets the filename. */
    public function getFilename(): string;

    /** Returns the file name minus the extension. */
    public function getFilenameWithoutExtension(): string;

    /** Gets a string containing the file extension, or an empty string if the file has no extension. */
    public function getExtension(): string;

    /** Get the file size. */
    public function getSize(): int;

    /**
     * Get the file size in a human readable format.
     *
     * @param bool $si Return results according to IEC standards (ie. 4.60 KiB) or SI standards (ie. 4.7 kb)
     */
    public function getSizeFormatted(bool $si = false): string;

    /** Gets the last modified time for the file, in a Unix timestamp. */
    public function getMTime(): int;
}
