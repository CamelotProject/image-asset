<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Exception;

use Camelot\ImageAsset\Exception\NoFallbackException;
use Camelot\ImageAsset\Exception\NotFoundHttpException;
use Camelot\ImageAsset\Filesystem\Filesystem;
use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Exception\NoFallbackException
 */
final class NoFallbackExceptionTest extends TestCase
{
    /** @var Filesystem */
    private $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = FilesystemMockBuilder::create()->createImages();
    }

    public function testGetMessage(): void
    {
        /** @var ImageInterface $image */
        $image = $this->filesystem->get('asset.jpg');
        static::assertStringContainsString(
            'There was an error with the thumbnail image requested',
            (new NoFallbackException($image, new NotFoundHttpException('asset.jpg')))->getMessage()
        );
    }

    public function testGetImage(): void
    {
        /** @var ImageInterface $image */
        $image = $this->filesystem->get('asset.jpg');
        static::assertSame($image, (new NoFallbackException($image, new NotFoundHttpException('asset.jpg')))->getImage());
    }
}
