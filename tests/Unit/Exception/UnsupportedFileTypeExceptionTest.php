<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Exception;

use Camelot\ImageAsset\Exception\UnsupportedFileTypeException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Exception\UnsupportedFileTypeException
 */
final class UnsupportedFileTypeExceptionTest extends TestCase
{
    public function test__construct(): void
    {
        $exception = new UnsupportedFileTypeException('image/jpeg', 'foo.jpg');

        static::assertSame('Unhandled image file: image/jpeg (foo.jpg)', $exception->getMessage());
    }
}
