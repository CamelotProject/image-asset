<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Exception;

use Camelot\ImageAsset\Exception\InvalidAliasException;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Attributes\Alias;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Exception\InvalidAliasException
 */
final class InvalidAliasExceptionTest extends TestCase
{
    public function testCreate(): void
    {
        $exception = InvalidAliasException::create('toad', ['princess' => true]);

        static::assertInstanceOf(InvalidAliasException::class, $exception);
        static::assertSame('Alias "toad" does not exist. Possible aliases are: princess', $exception->getMessage());
    }

    public function testCreateInvalidMatch(): void
    {
        $exception = InvalidAliasException::createInvalidMatch(Action::CROP, 400, 300, [new Alias('princess', Action::FIT, 768, 1024)]);
        static::assertInstanceOf(InvalidAliasException::class, $exception);

        static::assertRegExp('#Alias for .crop 400 x 300. does not exist.+\RPossible aliases are.+\R.+princess .fit 768x1024.#m', $exception->getMessage());
    }
}
