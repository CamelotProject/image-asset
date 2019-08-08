<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Responder;

use Camelot\ImageAsset\Exception\RuntimeException;
use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Responder\ImageResponder;
use Camelot\ImageAsset\Responder\ImageResponderInterface;
use Camelot\ImageAsset\Response\Response;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FallbackMockBuilder;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use Camelot\ImageAsset\Tests\Fixtures\Image\Attributes\AliasesMockBuilder;
use Camelot\ImageAsset\Tests\Fixtures\Transaction\TransactionMockBuilder;
use Camelot\ImageAsset\Thumbnail\Thumbnail;
use Camelot\ImageAsset\Thumbnail\ThumbnailInterface;
use Camelot\ImageAsset\Transaction\ProcessorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * @covers \Camelot\ImageAsset\Responder\ImageResponder
 */
final class ImageResponderTest extends TestCase
{
    /** @var ThumbnailInterface|MockObject */
    /** @var ThumbnailInterface */
    protected $thumbnail;
    /** @var ImageInterface */
    protected $image;
    /** @var ProcessorInterface|MockObject */
    /** @var ProcessorInterface */
    private $processor;

    protected function setUp(): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        /** @var ImageInterface $image */
        $this->image = $filesystem->get('default.png');
        $this->thumbnail = new Thumbnail($this->image, $this->image->read());
        $this->processor = $this->createMock(ProcessorInterface::class);
    }

    public function providerThumbnail(): iterable
    {
        $request = Request::create('/');

        yield 'Valid file' => [Response::HTTP_OK, $request, 'default.png', true];
        yield 'Valid file @2x' => [Response::HTTP_OK, $request, 'default@2x.png', true];
        yield 'Invalid file' => [Response::HTTP_NOT_FOUND, $request, 'invalid.ext', false];
    }

    /** @dataProvider providerThumbnail */
    public function testGetThumbnail(int $expected, Request $request, string $file, bool $isValidFile): void
    {
        $returnFile = $isValidFile ? $file : 'default.png';
        /** @var ImageInterface $image */
        $image = FilesystemMockBuilder::create()->createImages()->get(str_replace('@2x', '', $returnFile));

        $this->processor
            ->expects(static::atLeastOnce())
            ->method('process')
            ->willReturn(new Thumbnail($image, $image->read()))
        ;
        $responder = $this->getImageResponder(false);
        $response = $responder->getThumbnail($request, $file, Action::CROP, 400, 300);

        static::assertInstanceOf(Response::class, $response);
        $this->assertHttpStatusCode($expected, $response);
    }

    public function providerThumbnailAlias(): iterable
    {
        $request = Request::create('');

        yield 'Valid alias' => [Response::HTTP_OK, $request, 'default.png', 'test_128x128'];
        yield 'Invalid alias' => [Response::HTTP_FORBIDDEN, $request, 'default.png', 'wrong_128x128'];
        yield 'Valid alias, invalid file' => [Response::HTTP_NOT_FOUND, $request, 'invalid.ext', 'test_128x128'];
    }

    /** @dataProvider providerThumbnailAlias */
    public function testGetThumbnailFromAlias(int $expected, Request $request, string $file, string $alias): void
    {
        $responder = $this->getImageResponder(false);
        /** @var ImageInterface $image */
        $image = FilesystemMockBuilder::create()->createImages()->get('default.png');
        $this->processor
            ->expects(static::atLeastOnce())
            ->method('process')
            ->willReturn(new Thumbnail($image, $image->read()))
        ;
        $response = $responder->getThumbnailFromAlias($request, $file, $alias);

        static::assertInstanceOf(Response::class, $response);
        $this->assertHttpStatusCode($expected, $response);
    }

    public function testDefaultResponse(): void
    {
        /** @var ImageInterface $image */
        $image = FilesystemMockBuilder::create()->createImages()->get('400x300/crop/default.png');
        $this->processor
            ->expects(static::at(0))
            ->method('process')
            ->willThrowException(new RuntimeException())
        ;
        $this->processor
            ->expects(static::at(1))
            ->method('process')
            ->willReturn(new Thumbnail($image, $image->read()))
        ;
        $responder = $this->getImageResponder(false);
        $response = $responder->getThumbnail(Request::create(''), 'error.png', Action::CROP, 400, 300);

        self::assertImagesSame('400x300/crop/default.png', $response->getContent());
    }

    public function testErrorResponse(): void
    {
        /** @var ImageInterface $image */
        $image = FilesystemMockBuilder::create()->createImages()->get('400x300/crop/error.png');
        $this->processor
            ->expects(static::at(0))
            ->method('process')
            ->willThrowException(new RuntimeException())
        ;
        $this->processor
            ->expects(static::at(1))
            ->method('process')
            ->willReturn(new Thumbnail($image, $image->read()))
        ;
        $responder = $this->getImageResponder(false);
        $response = $responder->getThumbnail(Request::create(''), 'default.png', Action::CROP, 1024, 768);

        self::assertImagesSame('400x300/crop/error.png', $response->getContent());
    }

    private function getImageResponder(bool $debug): ImageResponderInterface
    {
        return new ImageResponder(
            TransactionMockBuilder::createBuilder(),
            $this->processor,
            FallbackMockBuilder::create(),
            FilesystemMockBuilder::create()->createImages(),
            new NullLogger(),
            AliasesMockBuilder::create(),
            0,
            $debug
        );
    }

    private function assertImagesSame(string $targetFile, string $responseData): void
    {
        $file = FilesystemMockBuilder::create()->createImages()->get($targetFile);
        if ($file->read() !== $responseData) {
            static::fail(sprintf('Response does not match requested file %s', $targetFile));
        }
    }

    private function assertHttpStatusCode(int $expected, SymfonyResponse $response): void
    {
        if ($expected !== $response->getStatusCode()) {
            static::fail("Incorrect HTTP status code. Expected $expected, got {$response->getStatusCode()}");
        } else {
            $this->addToAssertionCount(1);
        }
    }
}
