<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Image\Attributes;

use Camelot\ImageAsset\Exception\InvalidArgumentException;

/**
 * An object representation of a color.
 */
final class Color
{
    /** @var ?int */
    private $index;
    /** @var int */
    private $red;
    /** @var int */
    private $green;
    /** @var int */
    private $blue;
    /** @var ?int */
    private $alpha;

    /**
     * @param int      $red   Value of red component (between 0 and 255)
     * @param int      $green Value of green component (between 0 and 255)
     * @param int      $blue  Value of blue component (between 0 and 255)
     * @param null|int $alpha Optional value of alpha component (between 0 and 127). 0 = opaque, 127 = transparent.
     * @param null|int $index Index of the color for the image resource
     */
    public function __construct(int $red, int $green, int $blue, ?int $alpha = null, ?int $index = null)
    {
        foreach ([$red, $green, $blue] as $component) {
            if ($component < 0 || $component > 255) {
                throw new InvalidArgumentException('Color components are expected to be between 0 and 255');
            }
        }
        if ($alpha !== null) {
            if ($alpha < 0 || $alpha > 127) {
                throw new InvalidArgumentException('Color alpha component is expected to be between 0 and 127');
            }
        }

        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
        $this->alpha = $alpha;
        $this->index = $index;
    }

    /** Shortcut to create a transparent color. */
    public static function transparent(): self
    {
        return new static(0, 0, 0, 127);
    }

    /** Shortcut to create a white color. */
    public static function white(): self
    {
        return new static(255, 255, 255);
    }

    public function getRed(): int
    {
        return $this->red;
    }

    public function getGreen(): int
    {
        return $this->green;
    }

    public function getBlue(): int
    {
        return $this->blue;
    }

    public function getAlpha(): ?int
    {
        return $this->alpha;
    }

    public function getIndex(): ?int
    {
        return $this->index;
    }
}
