<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Transaction;

use Camelot\ImageAsset\Exception\BadMethodCallException;
use Camelot\ImageAsset\Exception\IOException;
use Camelot\ImageAsset\Filesystem\FilesystemInterface;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use Camelot\ImageAsset\Tests\Fixtures\Thumbnail\ThumbnailMockBuilder;
use Camelot\ImageAsset\Tests\Fixtures\Transaction\TransactionMockBuilder;
use Camelot\ImageAsset\Tests\ThumbnailAssertTrait;
use Camelot\ImageAsset\Thumbnail\CreatorInterface;
use Camelot\ImageAsset\Thumbnail\ThumbnailInterface;
use Camelot\ImageAsset\Transaction\Transaction;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Transaction\Processor
 */
final class ProcessorTest extends TestCase
{
    use ThumbnailAssertTrait;

    /** @var CreatorInterface */
    private $creator;
    /** @var FilesystemInterface */
    private $imagesFilesystem;
    /** @var FilesystemInterface */
    private $thumbsFilesystem;

    protected function setUp(): void
    {
        parent::setUp();

        $builder = FilesystemMockBuilder::create();
        $this->creator = $this->createMock(CreatorInterface::class);
        $this->imagesFilesystem = $builder->createImages();
        $this->thumbsFilesystem = $builder->createThumbs();
    }

    public function providerProcess(): iterable
    {
        yield 'First request (Uncached)' => ['/placeholder-128x128b.jpg', false];
        yield 'Subsequent request (Cached)' => ['/placeholder-128x128b.jpg', true];
    }

    /** @dataProvider providerProcess */
    public function testProcess(string $requestPath, bool $isHit): void
    {
        $processor = TransactionMockBuilder::createProcessor($this->creator);
        $transaction = TransactionMockBuilder::createTransaction($requestPath, Action::createBorder());
        if ($isHit) {
            $this->thumbsFilesystem->write(ThumbnailMockBuilder::createNameGenerator()->generate(256, 128, Action::BORDER, $requestPath), FilesystemMockBuilder::create()->createImages()->read($requestPath));
        } else {
            $this->creator
                ->expects(static::atLeastOnce())
                ->method('create')
                ->willReturn($this->imagesFilesystem->read($requestPath))
            ;
        }
        $thumbnail = $processor->process($transaction);

        static::assertInstanceOf(ThumbnailInterface::class, $thumbnail);
    }

    public function testProcessResult(): void
    {
        $expected = $this->imagesFilesystem->read('placeholder.jpg');
        $processor = TransactionMockBuilder::createProcessor($this->creator);
        $transaction = TransactionMockBuilder::createTransaction('placeholder.jpg', Action::createBorder());
        $this->creator
            ->expects(static::atLeastOnce())
            ->method('create')
            ->willReturn($expected)
        ;
        $thumbnail = $processor->process($transaction);

        static::assertEquals($expected, (string) $thumbnail);
    }

    public function testProcessCompleteTransaction(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot process a completed transaction.');

        $processor = TransactionMockBuilder::createProcessor($this->creator);
        $transaction = TransactionMockBuilder::createTransaction('/default.png', Action::createBorder());
        $transaction->start();
        $transaction->setResult(Transaction::PASS);

        $processor->process($transaction);
    }

    /** @dataProvider providerProcess */
    public function testSaveStatic(string $requestPath, bool $isHit): void
    {
        if ($isHit) {
            $this->thumbsFilesystem->write(ThumbnailMockBuilder::createNameGenerator()->generate(400, 300, Action::BORDER, $requestPath), $this->imagesFilesystem->get('placeholder.jpg'));
            $this->thumbsFilesystem = $this->createMock(FilesystemInterface::class);
            $this->thumbsFilesystem
                ->expects(static::at(0))
                ->method('exists')
                ->willReturn(true)
            ;
            $this->thumbsFilesystem
                ->expects(static::at(1))
                ->method('get')
                ->willReturn($this->imagesFilesystem->get('placeholder.jpg'))
            ;
            $this->creator
                ->expects(static::never())
                ->method('create')
            ;
        } else {
            $this->creator
                ->expects(static::once())
                ->method('create')
                ->willReturn($this->imagesFilesystem->read($requestPath))
            ;
        }
        $processor = TransactionMockBuilder::createProcessor($this->creator, $this->thumbsFilesystem);
        $transaction = TransactionMockBuilder::createTransaction($requestPath, Action::createBorder(), 400, 300);

        $thumbnail = $processor->process($transaction);

        static::assertFileExists($thumbnail->getImage()->getPathname());
    }

    public function testSaveStaticThumbnailWriteException(): void
    {
        $this->expectException(IOException::class);

        $this->thumbsFilesystem = $this->createMock(FilesystemInterface::class);
        $this->thumbsFilesystem
            ->expects(static::at(0))
            ->method('exists')
            ->willReturn(false)
        ;
        $this->thumbsFilesystem
            ->expects(static::at(1))
            ->method('write')
            ->willThrowException(new IOException(''))
        ;

        $processor = TransactionMockBuilder::createProcessor($this->creator, $this->thumbsFilesystem);
        $transaction = TransactionMockBuilder::createTransaction('/placeholder.jpg');

        $processor->process($transaction);
    }
}
