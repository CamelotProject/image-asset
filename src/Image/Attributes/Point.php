<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Image\Attributes;

/**
 * A value object which has X and Y coordinates.
 */
final class Point
{
    /** @var int */
    private $x;
    /** @var int */
    private $y;

    public function __construct(int $x = 0, int $y = 0)
    {
        $this->setX($x);
        $this->setY($y);
    }

    public function __toString(): string
    {
        return sprintf('(%d, %d)', $this->x, $this->y);
    }

    /** Returns the x-coordinate of the point. */
    public function getX(): int
    {
        return $this->x;
    }

    /** Sets the x-coordinate for this point. */
    public function setX(int $x): self
    {
        $this->x = $x;

        return $this;
    }

    /** Returns the y-coordinate of the point. */
    public function getY(): int
    {
        return $this->y;
    }

    /** Sets the x-coordinate for this point. */
    public function setY(int $y): self
    {
        $this->y = $y;

        return $this;
    }
}
