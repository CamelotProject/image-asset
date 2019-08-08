<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Image\Attributes;

final class Alias
{
    /** @var string */
    private $name;
    /** @var string */
    private $action;
    /** @var int */
    private $width;
    /** @var int */
    private $height;

    public function __construct(string $name, string $action, int $width, int $height)
    {
        $this->name = $name;
        $this->action = $action;
        $this->width = $width;
        $this->height = $height;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }
}
