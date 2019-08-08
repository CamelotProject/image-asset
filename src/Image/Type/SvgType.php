<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Image\Type;

/**
 * A SVG image type.
 */
final class SvgType implements TypeInterface
{
    public const ID = 101;
    public const MIME = 'image/svg+xml';

    /** @codeCoverageIgnore */
    private function __construct()
    {
    }

    /** {@inheritdoc} */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Returns a list of all the SVG image types.
     *
     * @return TypeInterface[]
     *
     * @psalm-return array<int, TypeInterface>
     */
    public static function getTypes(): array
    {
        $types[] = new static();

        return $types;
    }

    /** {@inheritdoc} */
    public function getId(): int
    {
        return self::ID;
    }

    /** {@inheritdoc} */
    public function getMimeType(): string
    {
        return self::MIME;
    }

    /** {@inheritdoc} */
    public function getExtension(bool $includeDot = true): string
    {
        return ($includeDot ? '.' : '') . 'svg';
    }

    /** {@inheritdoc} */
    public function toString(): string
    {
        return 'SVG';
    }
}
