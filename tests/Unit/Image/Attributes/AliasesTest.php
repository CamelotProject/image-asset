<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Image\Attributes;

use Camelot\ImageAsset\Exception\BadMethodCallException;
use Camelot\ImageAsset\Exception\InvalidAliasException;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Attributes\Alias;
use Camelot\ImageAsset\Image\Attributes\Aliases;
use Camelot\ImageAsset\Tests\Fixtures\Image\Attributes\AliasesMockBuilder;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Camelot\ImageAsset\Image\Attributes\Aliases
 */
final class AliasesTest extends TestCase
{
    public function testInvalidConstructorParameters(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Alias must be an instance of ' . Alias::class . ', stdClass passed');

        new Aliases([new stdClass()]);
    }

    public function testHasAlias(): void
    {
        $aliases = AliasesMockBuilder::create();
        $missing = [];
        foreach (['test_128x128', 'test_100x200', 'test_800x600', 'test_1600x900'] as $aliasName) {
            if (!$aliases->hasAlias($aliasName)) {
                $missing[] = $aliasName;
            }
        }
        static::assertEmpty($missing, sprintf('Did not find alias(es): %s', implode(', ', $missing)));
    }

    public function testHasAliasNot(): void
    {
        $aliases = AliasesMockBuilder::create();
        static::assertFalse($aliases->hasAlias('foobar'));
    }

    public function testGetAlias(): void
    {
        $aliases = AliasesMockBuilder::create();
        $alias = $aliases->getAlias('test_1600x900');

        static::assertSame('test_1600x900', $alias->getName());
        static::assertSame(Action::RESIZE, $alias->getAction());
    }

    public function testGetAliasInvalid(): void
    {
        $this->expectException(InvalidAliasException::class);
        $this->expectExceptionMessage('Alias "my_shopping_list" does not exist. Possible aliases are: test_128x128, test_100x200, test_800x600, test_1600x900');

        $aliases = AliasesMockBuilder::create();
        $aliases->getAlias('my_shopping_list');
    }

    public function testMatch(): void
    {
        $aliases = AliasesMockBuilder::create();
        /** @var Alias $alias */
        foreach (AliasesMockBuilder::getAliasIterable() as $alias) {
            $aliases->match($alias->getWidth(), $alias->getHeight(), $alias->getAction());
            $this->addToAssertionCount(1);
        }
    }

    public function testMatchFail(): void
    {
        $this->expectException(InvalidAliasException::class);
        $this->expectExceptionMessageRegExp('#Alias for "crop 1 x 1" does not exist.\RPossible aliases are#m');

        $aliases = AliasesMockBuilder::create();
        $aliases->match(1, 1, 'crop');
    }

    public function testGetAliases(): void
    {
        $aliases = AliasesMockBuilder::create();

        static::assertCount(4, $aliases->getAliases());
        foreach ($aliases->getAliases() as $alias) {
            static::assertInstanceOf(Alias::class, $alias);
        }
    }

    public function testGetRouterRegex(): void
    {
        $aliases = AliasesMockBuilder::create();

        static::assertSame('test_128x128|test_100x200|test_800x600|test_1600x900', $aliases->getRouterRegex());
    }
}
