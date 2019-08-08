<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Image;

/**
 * A value object which has a width and a height.
 */
final class Dimensions
{
    /** @var int */
    private $width;
    /** @var int */
    private $height;

    public function __construct(int $width = 0, int $height = 0)
    {
        $this->setWidth($width);
        $this->setHeight($height);
    }

    public function __toString(): string
    {
        return $this->width . ' × ' . $this->height . ' px';
    }

    /** Returns the width. */
    public function getWidth(): int
    {
        return $this->width;
    }

    /** Sets the width. */
    public function setWidth(int $width): self
    {
        $this->width = (int)$width;

        return $this;
    }

    /** Returns the height. */
    public function getHeight(): int
    {
        return $this->height;
    }

    /** Sets the height. */
    public function setHeight(int $height): self
    {
        $this->height = (int)$height;

        return $this;
    }
}
