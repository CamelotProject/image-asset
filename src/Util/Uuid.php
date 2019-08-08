<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Util;

final class Uuid
{
    /** @var string */
    private $uuid;

    private function __construct()
    {
        $this->uuid = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff)
        );
    }

    public static function uuid4(): self
    {
        return new self();
    }

    public function __toString(): string
    {
        return $this->uuid;
    }
}
