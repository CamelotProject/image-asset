<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Exception;

use Camelot\ImageAsset\Exception\ResolverIOException;
use Camelot\ImageAsset\Image\Attributes\Action;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Exception\ResolverIOException
 */
final class ResolverIOExceptionTest extends TestCase
{
    public function test__construct(): void
    {
        $exception = new ResolverIOException(
            (string) Action::createCrop(),
            [
                new Exception('First exception message'),
                new Exception('Second exception message'),
            ],
            'foo.jpg'
        );

        static::assertStringContainsString('Unable to perform crop operation on foo.jpg', $exception->getMessage());
        static::assertStringContainsString('First exception message', $exception->getMessage());
        static::assertStringContainsString('Second exception message', $exception->getMessage());
    }
}
