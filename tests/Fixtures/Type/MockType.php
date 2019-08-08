<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Fixtures\Type;

use Camelot\ImageAsset\Image\Type\TypeInterface;

final class MockType implements TypeInterface
{
    public const ID = 2209;
    public const MIME = 'image/princess';

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return self::ID;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType(): string
    {
        return self::MIME;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension(bool $includeDot = true): string
    {
        return ($includeDot ? '.' : '') . 'princess';
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return 'PRINCESS';
    }
}
