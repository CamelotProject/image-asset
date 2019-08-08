<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Thumbnail;

use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Image\Dimensions;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use Camelot\ImageAsset\Thumbnail\Thumbnail;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Thumbnail\Thumbnail
 */
final class ThumbnailTest extends TestCase
{
    /** @var string */
    private $data = '';
    /** @var ImageInterface */
    private $image;

    protected function setUp(): void
    {
        $this->data = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==', true);
    }

    public function testToString(): void
    {
        static::assertSame($this->data, (string) $this->getThumbnail());
    }

    public function testGetImage(): void
    {
        $thumbnail = $this->getThumbnail();
        static::assertSame($this->image, $thumbnail->getImage());
    }

    public function testGetThumbnail(): void
    {
        $thumbnail = $this->getThumbnail();
        static::assertSame($this->data, $thumbnail->getThumbnail());
    }

    public function providerThumbnailDimensions(): iterable
    {
        yield ['placeholder.jpg', 1200, 1200];
        yield ['svg.svg', 1000, 531];
    }

    /** @dataProvider providerThumbnailDimensions */
    public function testGetThumbnailDimensions(string $file): void
    {
        $data = FilesystemMockBuilder::create()->createImages()->read($file);
        $thumbnail = $this->getThumbnail($file, $data);
        $dimensions = $thumbnail->getThumbnailDimensions();

        static::assertInstanceOf(Dimensions::class, $dimensions);
        static::assertSame($dimensions, $thumbnail->getThumbnailDimensions(), sprintf('New %s object was created on repeat call.', Dimensions::class));
    }

    /** @dataProvider providerThumbnailDimensions */
    public function testGetThumbnailDimensionsWidth(string $file, int $width): void
    {
        $data = FilesystemMockBuilder::create()->createImages()->read($file);

        static::assertSame($width, $this->getThumbnail($file, $data)->getThumbnailDimensions()->getWidth());
    }

    /** @dataProvider providerThumbnailDimensions */
    public function testGetThumbnailDimensionsHeight(string $file, int $width, int $height): void
    {
        $data = FilesystemMockBuilder::create()->createImages()->read($file);

        static::assertSame($height, $this->getThumbnail($file, $data)->getThumbnailDimensions()->getHeight());
    }

    private function getThumbnail(string $file = 'default.png', ?string $data = null): Thumbnail
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        /** @var ImageInterface $image */
        $image = $filesystem->get($file);
        $this->image = $image;
        $data = $data ?: $this->data;

        return new Thumbnail($this->image, $data);
    }
}
