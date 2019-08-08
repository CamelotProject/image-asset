<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Image\Attributes;

use Camelot\ImageAsset\Filesystem\Filesystem;
use Camelot\ImageAsset\Image\Attributes\Exif;
use Camelot\ImageAsset\Image\Attributes\Info;
use Camelot\ImageAsset\Image\Dimensions;
use Camelot\ImageAsset\Image\Type\SvgType;
use Camelot\ImageAsset\Image\Type\Type;
use Camelot\ImageAsset\Image\Type\TypeInterface;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * @covers \Camelot\ImageAsset\Image\Attributes\Info
 */
final class InfoTest extends TestCase
{
    /** @var Filesystem */
    protected $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = FilesystemMockBuilder::create()->createImages();
    }

    public function testConstruct(): void
    {
        $exif = new Exif([]);
        $type = Type::getById(IMAGETYPE_JPEG);
        new Info(new Dimensions(1024, 768), $type, 2, 7, 'Marcel Marceau', $exif);
        $this->addToAssertionCount(1);
    }

    public function testCreateFromFile(): void
    {
        $info = Info::createFromFile($this->filesystem->getMountPath() . '/asset.jpg');

        static::assertInstanceOf(Info::class, $info);
        static::assertInstanceOf(TypeInterface::class, $info->getType());
        static::assertInstanceOf(Exif::class, $info->getExif());

        static::assertSame(400, $info->getWidth());
        static::assertSame(200, $info->getHeight());
        static::assertSame(8, $info->getBits());
        static::assertSame(3, $info->getChannels());
        static::assertSame('image/jpeg', $info->getMime());
        static::assertSame(2.0, $info->getAspectRatio());

        static::assertTrue($info->isLandscape());
        static::assertFalse($info->isPortrait());
        static::assertFalse($info->isSquare());
        static::assertTrue($info->isValid());
    }

    public function providerFileValidity(): iterable
    {
        yield [true, 'generic/generic-logo.jpg'];
        yield [true, 'generic/generic-logo.gif'];
        yield [true, 'generic/generic-logo.png'];
        yield [true, 'svg.svg'];
        yield [true, 'svg-undeclared.svg'];
        yield [false, 'empty.jpg'];
    }

    /** @dataProvider providerFileValidity */
    public function testCreateFromFileValidity(bool $expected, string $fileName): void
    {
        $info = Info::createFromFile($this->filesystem->getMountPath() . '/' . $fileName);

        static::assertSame($expected, $info->isValid());
    }

    public function testCreateFromFileEmpty(): void
    {
        $info = Info::createFromFile($this->filesystem->getMountPath() . '/empty.jpg');

        static::assertSame(0, $info->getWidth());
        static::assertSame(0, $info->getHeight());
        static::assertSame(0, $info->getBits());
        static::assertSame(0, $info->getChannels());
        static::assertNull($info->getMime());
        static::assertSame(0.0, $info->getAspectRatio());
        static::assertFalse($info->isValid());
    }

    public function testCreateFromFileInvalid(): void
    {
        $info = Info::createFromFile('drop-bear.jpg');

        static::assertFalse($info->isValid());
    }

    public function testCreateFromString(): void
    {
        $file = $this->filesystem->read('reverse.jpg');
        $info = Info::createFromString($file);

        static::assertInstanceOf(Info::class, $info);
        static::assertInstanceOf(TypeInterface::class, $info->getType());
        static::assertInstanceOf(Exif::class, $info->getExif());

        static::assertSame(400, $info->getWidth());
        static::assertSame(200, $info->getHeight());
        static::assertSame(8, $info->getBits());
        static::assertSame(3, $info->getChannels());
        static::assertSame('image/jpeg', $info->getMime());
        static::assertSame(0.5, $info->getAspectRatio());

        static::assertFalse($info->isLandscape());
        static::assertTrue($info->isPortrait());
        static::assertFalse($info->isSquare());
        static::assertTrue($info->isValid());
    }

    public function testCreateFromStringEmpty(): void
    {
        $file = $this->filesystem->read('empty.jpg');

        $info = Info::createFromString($file, 'empty.jpg');

        static::assertSame(0, $info->getWidth());
        static::assertSame(0, $info->getHeight());
        static::assertSame(0, $info->getBits());
        static::assertSame(0, $info->getChannels());
        static::assertNull($info->getMime());
        static::assertSame(0.0, $info->getAspectRatio());
        static::assertFalse($info->isValid());
    }

    public function testCreateFromStringInvalid(): void
    {
        $info = Info::createFromString('Z2h', 'drop-bear.jpg');

        static::assertFalse($info->isValid());
    }

    public function testClone(): void
    {
        $file = $this->filesystem->read('asset.jpg');
        $info = Info::createFromString($file);
        $clone = clone $info;

        static::assertNotSame($clone->getExif(), $info->getExif());
    }

    public function testSerialize(): void
    {
        $file = $this->filesystem->read('asset.jpg');
        $expected = Info::createFromString($file);
        /** @var Info $actual */
        $actual = unserialize(serialize($expected));
        $this->assertInfoEquals($expected, $actual);
    }

    public function testJsonSerialize(): void
    {
        $file = $this->filesystem->read('asset.jpg');
        $expected = Info::createFromString($file);
        $actual = Info::createFromJson(json_decode(json_encode($expected), true));

        $this->assertInfoEquals($expected, $actual);
    }

    public function testSvgFromString(): void
    {
        $file = $this->filesystem->read('svg.svg');
        $info = Info::createFromString($file);

        static::assertSame(1000, $info->getWidth());
        static::assertSame(531, $info->getHeight());
        static::assertSame('image/svg+xml', $info->getMime());
        static::assertTrue($info->isValid());
        static::assertInstanceOf(SvgType::class, $info->getType());
    }

    public function testSvgFromFile(): void
    {
        $info = Info::createFromFile($this->filesystem->getMountPath() . '/svg.svg');

        static::assertSame(1000, $info->getWidth());
        static::assertSame(531, $info->getHeight());
        static::assertSame('image/svg+xml', $info->getMime());
        static::assertTrue($info->isValid());
        static::assertInstanceOf(SvgType::class, $info->getType());
    }

    public function testSvgWithoutXmlDeclaration(): void
    {
        $data = $this->filesystem->read('svg.svg');
        $data = substr($data, 39);
        $info = Info::createFromString($data);

        static::assertSame(1000, $info->getWidth());
        static::assertSame(531, $info->getHeight());
        static::assertSame('image/svg+xml', $info->getMime());
        static::assertTrue($info->isValid());
        static::assertInstanceOf(SvgType::class, $info->getType());
    }

    public function testReadExif(): void
    {
        $info = Info::createFromFile($this->filesystem->getMountPath() . '/empty.jpg');

        $m = new ReflectionMethod(Info::class, 'readExif');
        $m->setAccessible(true);

        $exif = $m->invoke($info, $this->filesystem->getMountPath() . '/empty.jpg');
        static::assertInstanceOf(Exif::class, $exif);
    }

    private function assertInfoEquals(Info $expected, Info $actual): void
    {
        static::assertEquals($expected->getDimensions(), $actual->getDimensions());
        static::assertSame($expected->getType(), $actual->getType());
        static::assertSame($expected->getBits(), $actual->getBits());
        static::assertSame($expected->getChannels(), $actual->getChannels());
        static::assertSame($expected->getMime(), $actual->getMime());
        static::assertEquals($expected->getExif()->getData(), $actual->getExif()->getData());
        static::assertSame($expected->isValid(), $actual->isValid());
    }
}
