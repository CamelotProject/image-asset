<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Exception;

use Camelot\ImageAsset\Exception\IOException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Exception\IOException
 */
final class IOExceptionTest extends TestCase
{
    public function testGetPath(): void
    {
        static::assertSame('/road/to/nowhere', (new IOException('Not found', '/road/to/nowhere'))->getPath());
    }
}
