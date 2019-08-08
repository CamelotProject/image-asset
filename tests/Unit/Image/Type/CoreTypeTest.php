<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Image\Type;

use Camelot\ImageAsset\Image\Type\CoreType;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Image\Type\CoreType
 */
final class CoreTypeTest extends TestCase
{
    public function providerTypeStrings(): iterable
    {
        yield ['BMP', IMAGETYPE_BMP];
        yield ['GIF', IMAGETYPE_GIF];
        yield ['PNG', IMAGETYPE_PNG];
        yield ['JPEG', IMAGETYPE_JPEG];
        yield ['WEBP', IMAGETYPE_WEBP];
    }

    /** @dataProvider providerTypeStrings */
    public function testToString(string $expected, int $typeId): void
    {
        $type = CoreType::getTypes()[$typeId];
        static::assertSame($expected, (string) $type);
    }

    public function providerIds(): iterable
    {
        yield [IMAGETYPE_BMP, IMAGETYPE_BMP];
        yield [IMAGETYPE_GIF, IMAGETYPE_GIF];
        yield [IMAGETYPE_PNG, IMAGETYPE_PNG];
        yield [IMAGETYPE_JPEG, IMAGETYPE_JPEG];
        yield [IMAGETYPE_WEBP, IMAGETYPE_WEBP];
    }

    /** @dataProvider providerIds */
    public function testGetId(int $expected, int $typeId): void
    {
        /** @var CoreType $type */
        $type = CoreType::getTypes()[$typeId];
        static::assertSame($expected, $type->getId());
    }

    public function providerMimeTypes(): iterable
    {
        yield ['image/bmp', IMAGETYPE_BMP];
        yield ['image/gif', IMAGETYPE_GIF];
        yield ['image/png', IMAGETYPE_PNG];
        yield ['image/jpeg', IMAGETYPE_JPEG];
        yield ['image/webp', IMAGETYPE_WEBP];
    }

    /** @dataProvider providerMimeTypes */
    public function testGetMimeType(string $expected, int $typeId): void
    {
        /** @var CoreType $type */
        $type = CoreType::getTypes()[$typeId];
        static::assertSame($expected, $type->getMimeType());
    }

    public function providerExtensions(): iterable
    {
        yield ['.bmp', IMAGETYPE_BMP];
        yield ['.gif', IMAGETYPE_GIF];
        yield ['.png', IMAGETYPE_PNG];
        yield ['.jpeg', IMAGETYPE_JPEG];
        yield ['.webp', IMAGETYPE_WEBP];
    }

    /** @dataProvider providerExtensions */
    public function testGetExtension(string $expected, int $typeId): void
    {
        /** @var CoreType $type */
        $type = CoreType::getTypes()[$typeId];
        static::assertSame($expected, $type->getExtension());
    }
}
