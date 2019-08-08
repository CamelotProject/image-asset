<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Image;

use Camelot\ImageAsset\Exception\InvalidArgumentException;
use Camelot\ImageAsset\Image\Type\SvgType;
use Camelot\ImageAsset\Image\Type\Type;
use Camelot\ImageAsset\Image\Type\TypeInterface;
use Camelot\ImageAsset\Tests\Fixtures\Type\MockType;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Camelot\ImageAsset\Image\Type\Type
 */
final class TypeTest extends TestCase
{
    public function testRegister(): void
    {
        $type = new MockType();
        Type::register($type);

        static::assertSame($type, Type::getById($type->getId()));
    }

    public function testGetById(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Given type is not an IMAGETYPE_* constant');

        $type = Type::getById(IMAGETYPE_JPEG);
        static::assertInstanceOf(TypeInterface::class, $type);

        $type2 = Type::getById(IMAGETYPE_JPEG);
        static::assertSame($type, $type2);

        Type::getById(42);
    }

    public function testToId(): void
    {
        $type = Type::getById(IMAGETYPE_JPEG);
        static::assertSame(2, $type->getId());
    }

    public function testToMimeType(): void
    {
        $type = Type::getById(IMAGETYPE_JPEG);
        static::assertSame('image/jpeg', $type->getMimeType());
    }

    public function testToExtension(): void
    {
        $type = Type::getById(IMAGETYPE_JPEG);
        static::assertSame('.jpeg', $type->getExtension(true));
        static::assertSame('jpeg', $type->getExtension(false));
    }

    public function testToString(): void
    {
        $type = Type::getById(IMAGETYPE_JPEG);
        static::assertSame('JPEG', $type->toString());
        static::assertSame('JPEG', (string) $type);
    }

    public function testSvg(): void
    {
        $type = Type::getById(SvgType::ID);
        static::assertEquals(101, $type->getId());
        static::assertEquals('image/svg+xml', $type->getMimeType());
        static::assertEquals('.svg', $type->getExtension());
        static::assertEquals('svg', $type->getExtension(false));
        static::assertEquals('SVG', $type->toString());
        static::assertEquals('SVG', (string) $type);
    }

    public function testGetTypes(): void
    {
        $types = Type::getTypes();
        static::assertInstanceOf(TypeInterface::class, $types[0]);
    }

    public function testGetMimeTypes(): void
    {
        $mimeTypes = Type::getMimeTypes();
        static::assertContains('image/jpeg', $mimeTypes);
    }

    public function providerExtensions(): iterable
    {
        yield ['bmp'];
        yield ['gif'];
        yield ['png'];
        yield ['jpeg'];
        yield ['jpg'];
        yield ['webp'];
    }

    /** @dataProvider providerExtensions */
    public function testGetExtensions(string $expected): void
    {
        $extensions = Type::getExtensions();
        static::assertContains($expected, $extensions);
    }

    public function testInitialize(): void
    {
        $rc = new ReflectionClass(Type::class);
        $rpTypes = $rc->getProperty('types');
        $rpTypes->setAccessible(true);
        $rpTypes->setValue([]);
        $rpInitialized = $rc->getProperty('initialized');
        $rpInitialized->setAccessible(true);
        $rpInitialized->setValue(false);

        /** @var TypeInterface $type */
        $type = Type::getTypes()[2];
        static::assertSame('JPEG', (string) $type);
    }
}
