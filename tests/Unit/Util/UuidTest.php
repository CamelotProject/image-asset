<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Util;

use Camelot\ImageAsset\Util\Uuid;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Util\Uuid
 */
final class UuidTest extends TestCase
{
    public function testCastTOString(): void
    {
        static::assertRegExp(
            '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i',
            (string) Uuid::uuid4()
        );
    }
}
