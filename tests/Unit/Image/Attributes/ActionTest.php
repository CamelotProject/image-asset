<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Image\Attributes;

use Camelot\ImageAsset\Exception\InvalidArgumentException;
use Camelot\ImageAsset\Image\Attributes\Action;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Image\Attributes\Action
 */
final class ActionTest extends TestCase
{
    public function test__toString(): void
    {
        static::assertSame(Action::CROP, (string) Action::createCrop());
    }

    public function testIsBorder(): void
    {
        $action = Action::createBorder();

        static::assertTrue($action->isBorder());

        static::assertFalse($action->isCrop());
        static::assertFalse($action->isFit());
        static::assertFalse($action->isResize());
    }

    public function testIsCrop(): void
    {
        $action = Action::createCrop();

        static::assertTrue($action->isCrop());

        static::assertFalse($action->isBorder());
        static::assertFalse($action->isFit());
        static::assertFalse($action->isResize());
    }

    public function testIsFit(): void
    {
        $action = Action::createFit();

        static::assertTrue($action->isFit());

        static::assertFalse($action->isBorder());
        static::assertFalse($action->isCrop());
        static::assertFalse($action->isResize());
    }

    public function testIsResize(): void
    {
        $action = Action::createResize();

        static::assertTrue($action->isResize());

        static::assertFalse($action->isBorder());
        static::assertFalse($action->isCrop());
        static::assertFalse($action->isFit());
    }

    public function providerResolve(): iterable
    {
        yield 'b' => ['b', Action::BORDER];
        yield 'c' => ['c', Action::CROP];
        yield 'f' => ['f', Action::FIT];
        yield 'r' => ['r', Action::RESIZE];
    }

    /** @dataProvider providerResolve */
    public function testResolve(string $action, $expected): void
    {
        static::assertSame($expected, Action::resolve($action));
    }

    public function testResolveInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Action::create('invalid');
    }

    public function testResolveInvalidViaConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Action::resolve('invalid');
    }
}
