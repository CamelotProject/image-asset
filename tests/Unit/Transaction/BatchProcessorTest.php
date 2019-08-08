<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Transaction;

use Camelot\ImageAsset\Exception\UnsupportedFileTypeException;
use Camelot\ImageAsset\Filesystem\FilesystemInterface;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use Camelot\ImageAsset\Tests\Fixtures\Transaction\TransactionMockBuilder;
use Camelot\ImageAsset\Thumbnail\ThumbnailInterface;
use Camelot\ImageAsset\Transaction\Batch;
use Camelot\ImageAsset\Transaction\BatchProcessor;
use Camelot\ImageAsset\Transaction\ProcessorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function is_array;

/**
 * @covers \Camelot\ImageAsset\Transaction\BatchProcessor
 */
final class BatchProcessorTest extends TestCase
{
    /** @var ProcessorInterface|MockObject */
    /** @var ProcessorInterface */
    private $processor;
    /** @var FilesystemInterface */
    private $filesystem;

    protected function setUp(): void
    {
        $this->processor = $this->createMock(ProcessorInterface::class);
        parent::setUp();
    }

    public function testCreateBatch(): void
    {
        $builder = FilesystemMockBuilder::create();
        $filesystem = $builder->createImages();
        $processor = $this->getBatchProcessor();
        $batch = $processor->createBatch($filesystem->finder(), 22, 44, Action::CROP);

        static::assertInstanceOf(Batch::class, $batch);
    }

    /**
     * @depends testCreateBatch
     */
    public function testProcess(): void
    {
        $builder = FilesystemMockBuilder::create();
        $filesystem = $builder->createImages();
        $processor = $this->getBatchProcessor();
        $transaction = TransactionMockBuilder::createTransaction('default.png', Action::createCrop(), 22, 44);
        $batch = $processor->createBatch($filesystem->finder(), 22, 44, Action::CROP);
        $batch->addTransaction($transaction);
        $this->processor
            ->expects(static::atLeastOnce())
            ->method('process')
            ->willReturn($this->createMock(ThumbnailInterface::class))
        ;
        $processor->process($batch);
    }

    /**
     * @depends testCreateBatch
     */
    public function testProcessUnsupportedFileTypeException(): void
    {
        $builder = FilesystemMockBuilder::create();
        $filesystem = $builder->createImages();
        $processor = $this->getBatchProcessor();
        $transaction = TransactionMockBuilder::createTransaction('default.png', Action::createCrop(), 22, 44);
        $batch = $processor->createBatch($filesystem->finder(), 22, 44, Action::CROP);
        $batch->addTransaction($transaction);

        $this->processor
            ->expects(static::atLeastOnce())
            ->method('process')
            ->willThrowException(new UnsupportedFileTypeException('something/invalid', 'default.png'))
        ;

        $processor->process($batch);

        $es = $batch->getExceptions();
        $e = is_array($es) ? $es : iterator_to_array($es);

        static::assertInstanceOf(UnsupportedFileTypeException::class, array_pop($e));
    }

    private function getBatchProcessor(): BatchProcessor
    {
        return new BatchProcessor(TransactionMockBuilder::createBuilder(), $this->processor);
    }
}
