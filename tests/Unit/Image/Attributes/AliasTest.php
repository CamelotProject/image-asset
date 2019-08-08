<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Image\Attributes;

use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Attributes\Alias;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Image\Attributes\Alias
 */
final class AliasTest extends TestCase
{
    public function providerAliases(): iterable
    {
        yield ['test_128x128', Action::BORDER, 128, 128];
        yield ['test_100x200', Action::CROP, 100, 200];
        yield ['test_800x600', Action::FIT, 800, 600];
        yield ['test_1600x900', Action::RESIZE, 1600, 900];
    }

    /** @dataProvider providerAliases */
    public function testGetName(string $name, string $action, int $width, int $height): void
    {
        static::assertSame($name, (new Alias($name, $action, $width, $height))->getName());
    }

    /** @dataProvider providerAliases */
    public function testGetAction(string $name, string $action, int $width, int $height): void
    {
        static::assertSame($action, (new Alias($name, $action, $width, $height))->getAction());
    }

    /** @dataProvider providerAliases */
    public function testGetWidth(string $name, string $action, int $width, int $height): void
    {
        static::assertSame($width, (new Alias($name, $action, $width, $height))->getWidth());
    }

    /** @dataProvider providerAliases */
    public function testGetHeight(string $name, string $action, int $width, int $height): void
    {
        static::assertSame($height, (new Alias($name, $action, $width, $height))->getHeight());
    }
}
