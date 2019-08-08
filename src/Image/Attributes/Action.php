<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Image\Attributes;

use Camelot\ImageAsset\Exception\InvalidArgumentException;
use function in_array;

/**
 * Actions used when creating thumbnails.
 */
final class Action
{
    public const BORDER = 'border';
    public const CROP = 'crop';
    public const FIT = 'fit';
    public const RESIZE = 'resize';

    /** @var string */
    private $action;

    private function __construct(string $action)
    {
        $this->action = self::resolve($action);
    }

    public function __toString(): string
    {
        return $this->action;
    }

    public static function create(string $action): self
    {
        return new self($action);
    }

    public static function createBorder(): self
    {
        return new self(self::BORDER);
    }

    public static function createCrop(): self
    {
        return new self(self::CROP);
    }

    public static function createFit(): self
    {
        return new self(self::FIT);
    }

    public static function createResize(): self
    {
        return new self(self::RESIZE);
    }

    public function isBorder(): bool
    {
        return $this->action === self::BORDER;
    }

    public function isCrop(): bool
    {
        return $this->action === self::CROP;
    }

    public function isFit(): bool
    {
        return $this->action === self::FIT;
    }

    public function isResize(): bool
    {
        return $this->action === self::RESIZE;
    }

    public static function resolve(string $action): string
    {
        $actions = [
            'b' => self::BORDER,
            'c' => self::CROP,
            'f' => self::FIT,
            'r' => self::RESIZE,
        ];
        if (in_array($action, $actions, true)) {
            return $action;
        }
        if (isset($actions[$action])) {
            return $actions[$action];
        }

        throw new InvalidArgumentException(sprintf('Invalid action "%s" given. Possible values are %s or the aliases %s', $action, implode(', ', $actions), implode(', ', array_keys($actions))));
    }
}
