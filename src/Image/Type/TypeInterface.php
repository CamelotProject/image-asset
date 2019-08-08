<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Image\Type;

/**
 * An object representation of an image type.
 */
interface TypeInterface
{
    /** Returns the name of this type. */
    public function __toString(): string;

    /** Returns the ID. (probably IMAGETYPE_* constant). */
    public function getId(): int;

    /** Returns the MIME Type associated with this type. */
    public function getMimeType(): string;

    /**
     * Returns the file extension for this type.
     *
     * @param bool $includeDot Whether to prepend a dot to the extension or not
     */
    public function getExtension(bool $includeDot = true): string;

    /** Returns the name of this type. */
    public function toString(): string;
}
