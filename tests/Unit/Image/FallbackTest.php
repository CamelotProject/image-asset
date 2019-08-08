<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Image;

use Camelot\ImageAsset\Filesystem\Filesystem;
use Camelot\ImageAsset\Image\Dimensions;
use Camelot\ImageAsset\Image\Fallback;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Image\Fallback
 */
final class FallbackTest extends TestCase
{
    /** @var Filesystem */
    protected $filesystem;
    /** @var Dimensions */
    protected $dimensions;

    protected function setUp(): void
    {
        $this->filesystem = FilesystemMockBuilder::create()->createImages();
        $this->dimensions = new Dimensions(512, 512);
    }

    public function testGetDefaultDimensions(): void
    {
        static::assertEquals($this->dimensions, $this->getFallback()->getDefaultDimensions());
    }

    public function testGetDefaultImage(): void
    {
        static::assertEquals($this->filesystem->get('default.png'), $this->getFallback()->getDefaultImage());
    }

    public function testGetErrorImage(): void
    {
        static::assertEquals($this->filesystem->get('error.png'), $this->getFallback()->getErrorImage());
    }

    public function getFallback(): Fallback
    {
        return new Fallback($this->filesystem, $this->dimensions, 'default.png', 'error.png');
    }
}
