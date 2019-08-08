<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Exception;

use Camelot\ImageAsset\Image\Attributes\Alias;
use Throwable;
use const PHP_EOL;

class InvalidAliasException extends \RuntimeException implements ExceptionInterface
{
    /** @param Alias[] $aliases */
    public static function create(string $name, array $aliases, $code = 0, Throwable $previous = null): self
    {
        return new self(sprintf('Alias "%s" does not exist. Possible aliases are: %s', $name, implode(', ', array_keys($aliases))), $code, $previous);
    }

    /** @param Alias[] $aliases */
    public static function createInvalidMatch(string $action, int $width, int $height, array $aliases, $code = 0, Throwable $previous = null): self
    {
        $message = sprintf('Alias for "%s %s x %s" does not exist.%sPossible aliases are:%s%s', $action, $width, $height, PHP_EOL, PHP_EOL, self::toPrintable($aliases));

        return new self($message, $code, $previous);
    }

    private static function toPrintable(array $aliases): string
    {
        $output = '';
        /** @var Alias $alias */
        foreach ($aliases as $alias) {
            $output .= '- ' . $alias->getName();
            $output .= ' (' . $alias->getAction() . ' ' . $alias->getWidth() . 'x' . $alias->getHeight() . ')' . PHP_EOL;
        }

        return $output;
    }
}
