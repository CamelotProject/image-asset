<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Image\Attributes;

use Camelot\ImageAsset\Exception\BadMethodCallException;
use Camelot\ImageAsset\Exception\InvalidAliasException;
use function get_class;
use function gettype;
use function is_object;

final class Aliases
{
    /** @var Alias[] */
    private $aliases = [];

    public function __construct(iterable $aliases)
    {
        /** @var Alias $alias */
        foreach ($aliases as $alias) {
            if (!$alias instanceof Alias) {
                throw new BadMethodCallException(sprintf('Alias must be an instance of %s, %s passed', Alias::class, is_object($alias) ? get_class($alias) : gettype($alias)));
            }
            $this->aliases[$alias->getName()] = $alias;
        }
    }

    /** @throws InvalidAliasException */
    public function match(int $width, int $height, string $action): Alias
    {
        foreach ($this->aliases as $name => $alias) {
            if ($alias->getWidth() === $width && $alias->getHeight() === $height && $alias->getAction() === $action) {
                return $alias;
            }
        }

        throw InvalidAliasException::createInvalidMatch($action, $width, $height, $this->aliases);
    }

    public function hasAlias(string $name): bool
    {
        return isset($this->aliases[$name]);
    }

    /** @throws InvalidAliasException */
    public function getAlias(string $name): Alias
    {
        if (isset($this->aliases[$name])) {
            return $this->aliases[$name];
        }

        throw InvalidAliasException::create($name, $this->aliases);
    }

    /** @return Alias[] */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    public function getRouterRegex(): string
    {
        return implode('|', array_keys(array_map(function (Alias $alias): string { return $alias->getName(); }, $this->aliases)));
    }
}
