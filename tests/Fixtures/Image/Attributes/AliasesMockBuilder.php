<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Fixtures\Image\Attributes;

use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Attributes\Alias;
use Camelot\ImageAsset\Image\Attributes\Aliases;

final class AliasesMockBuilder
{
    public static function create(iterable $aliases = null): Aliases
    {
        return new Aliases($aliases ?: self::getAliasIterable());
    }

    public static function getAliasIterable(): iterable
    {
        foreach (self::getMeta() as $name => $parameters) {
            yield new Alias($name, $parameters['action'], $parameters['width'], $parameters['height']);
        }
    }

    public static function getMeta(): iterable
    {
        yield 'test_128x128' => ['action' => Action::BORDER, 'width' => 128, 'height' => 128];
        yield 'test_100x200' => ['action' => Action::CROP, 'width' => 100, 'height' => 200];
        yield 'test_800x600' => ['action' => Action::FIT, 'width' => 800, 'height' => 600];
        yield 'test_1600x900' => ['action' => Action::RESIZE, 'width' => 1600, 'height' => 900];
    }
}
