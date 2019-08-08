<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Image\Type;

use Camelot\ImageAsset\Image\Type\SvgType;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Image\Type\SvgType
 */
final class SvgTypeTest extends TestCase
{
    public function testToString(): void
    {
        $type = SvgType::getTypes()[0];
        static::assertSame('SVG', (string) $type);
    }

    public function testGetId(): void
    {
        /** @var SvgType $type */
        $type = SvgType::getTypes()[0];
        static::assertSame(101, $type->getId());
    }

    public function testGetMimeType(): void
    {
        /** @var SvgType $type */
        $type = SvgType::getTypes()[0];
        static::assertSame('image/svg+xml', $type->getMimeType());
    }

    public function testGetExtension(): void
    {
        /** @var SvgType $type */
        $type = SvgType::getTypes()[0];
        static::assertSame('.svg', $type->getExtension());
    }
}
