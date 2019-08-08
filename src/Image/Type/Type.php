<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Image\Type;

use Camelot\ImageAsset\Exception\InvalidArgumentException;

/**
 * A singleton of image types.
 */
final class Type
{
    /** @var TypeInterface[] */
    /** @var array */
    private static $types = [];
    /** @var bool */
    private static $initialized = false;

    /** @codeCoverageIgnore */
    private function __construct()
    {
    }

    /** Register an Image Type. */
    public static function register(TypeInterface $type): void
    {
        static::$types[$type->getId()] = $type;
    }

    /**
     * Returns a Type for the ID.
     *
     * @param int $id An IMAGETYPE_* constant
     *
     * @throws InvalidArgumentException If the ID isn't a valid IMAGETYPE_* constant
     */
    public static function getById(int $id): TypeInterface
    {
        $id = (int) $id;
        $types = static::getTypes();

        if (!isset($types[$id])) {
            throw new InvalidArgumentException('Given type is not an IMAGETYPE_* constant');
        }

        return $types[$id];
    }

    /**
     * Returns a list of all the image types.
     *
     * @return TypeInterface[]
     */
    public static function getTypes(): array
    {
        static::initialize();

        return static::$types;
    }

    /**
     * Returns a list of all the MIME Types for images.
     */
    public static function getMimeTypes(): array
    {
        return array_map(function (TypeInterface $type) { return $type->getMimeType(); }, static::getTypes());
    }

    /**
     * Returns a list of all the file extensions for images.
     *
     * @param bool $includeDot Whether to prepend a dot to the extension or not
     */
    public static function getExtensions(bool $includeDot = false): array
    {
        $extensions = array_filter(array_map(function (TypeInterface $type) use ($includeDot) { return $type->getExtension($includeDot); }, static::getTypes()));
        $extensions[] = ($includeDot ? '.' : '') . 'jpg';

        return $extensions;
    }

    /** Shortcut for unknown image type. */
    public static function unknown(): TypeInterface
    {
        return static::getById(IMAGETYPE_UNKNOWN);
    }

    /** Register default types. */
    private static function initialize(): void
    {
        if (static::$initialized) {
            return;
        }
        static::$initialized = true;

        foreach (CoreType::getTypes() as $type) {
            static::register($type);
        }
        foreach (SvgType::getTypes() as $type) {
            static::register($type);
        }
    }
}
