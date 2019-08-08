<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Response;

use Camelot\ImageAsset\Filesystem\Filesystem;
use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Response\Response;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use Camelot\ImageAsset\Thumbnail\Thumbnail;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Response\Response
 */
final class ResponseTest extends TestCase
{
    /** @var Filesystem */
    protected $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = FilesystemMockBuilder::create()->createImages();
    }

    public function testSetThumbnail(): void
    {
        /** @var ImageInterface $image */
        $image = $this->filesystem->get('default.png');
        $thumbnail = new Thumbnail($image, 'data');
        $response = $this->getResponse();
        $response->setThumbnail($thumbnail, true);

        static::assertSame($thumbnail, $response->getThumbnail());
    }

    public function testSetThumbnailEtag(): void
    {
        /** @var ImageInterface $image */
        $image = $this->filesystem->get('default.png');
        $thumbnail = new Thumbnail($image, 'data');
        $response = $this->getResponse();
        $response->setThumbnail($thumbnail);
        $response->setAutoEtag();

        static::assertSame($thumbnail, $response->getThumbnail());
        static::assertSame('"a17c9aaa61e80a1bf71d0d850af4e5baa9800bbd"', $response->getEtag());
    }

    private function getResponse(): Response
    {
        /** @var ImageInterface $image */
        $image = $this->filesystem->get('default.png');
        $thumbnail = new Thumbnail($image, 'data');

        return Response::create($thumbnail);
    }
}
