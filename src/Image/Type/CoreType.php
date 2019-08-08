<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Image\Type;

use function defined;

/**
 * A core (built-in) image type.
 */
class CoreType implements TypeInterface
{
    /** @var int */
    private $id;
    /** @var string */
    private $name;

    /**
     * @param int    $id   An IMAGETYPE_* constant
     * @param string $name String representation based on constant
     */
    private function __construct(int $id, string $name)
    {
        $this->id = (int) $id;
        $this->name = $name;
    }

    /** {@inheritdoc} */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Returns a list of all the core image types.
     *
     * @return TypeInterface[]
     *
     * @psalm-return array<int, TypeInterface>
     */
    public static function getTypes(): array
    {
        $types = [];
        foreach (static::getConstants() as $id => $name) {
            $types[] = new static($id, $name);
        }

        return $types;
    }

    /** {@inheritdoc} */
    public function getId(): int
    {
        return $this->id;
    }

    /** {@inheritdoc} */
    public function getMimeType(): string
    {
        return image_type_to_mime_type($this->id);
    }

    /** {@inheritdoc} */
    public function getExtension(bool $includeDot = true): string
    {
        return image_type_to_extension($this->id, $includeDot) ?: '';
    }

    /** {@inheritdoc} */
    public function toString(): string
    {
        return $this->name;
    }

    /**
     * Returns a list of all the image type constants.
     *
     * @return string[] - [int $id, string $name]
     *
     * @psalm-return array<string, string>
     */
    private static function getConstants(): array
    {
        // Get list of all standard constants
        $constants = get_defined_constants(true);
        if (defined('HHVM_VERSION')) {
            $constants = $constants['Core']; // @codeCoverageIgnore
        } else {
            $constants = $constants['standard'];
        }

        // filter down to image type constants
        $types = [];
        foreach ($constants as $name => $value) {
            if ($value !== IMAGETYPE_COUNT && strpos($name, 'IMAGETYPE_') === 0) {
                $types[$name] = $value;
            }
        }
        // flip these and map them to a humanized string
        $types = array_map(function (string $type) { return str_replace(['IMAGETYPE_', '_'], ['', ' '], $type); }, array_flip($types));
        ksort($types);

        return $types;
    }
}
