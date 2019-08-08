<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Transaction;

use Camelot\ImageAsset\Exception\BadMethodCallException;
use Camelot\ImageAsset\Filesystem\FileInterface;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Tests\Fixtures\Transaction\TransactionMockBuilder;
use Camelot\ImageAsset\Thumbnail\ThumbnailInterface;
use Camelot\ImageAsset\Transaction\Batch;
use Exception;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

/**
 * @covers \Camelot\ImageAsset\Transaction\Batch
 */
final class BatchTest extends TestCase
{
    /** @var array */
    private $files;

    protected function setUp(): void
    {
        $this->files = [new SplFileInfo(__FILE__)];
    }

    public function testGetFiles(): void
    {
        static::assertSame($this->files, $this->getBatch()->getFiles());
    }

    public function testGetWidth(): void
    {
        static::assertSame(22, $this->getBatch()->getWidth());
    }

    public function testGetHeight(): void
    {
        static::assertSame(44, $this->getBatch()->getHeight());
    }

    public function testGetAction(): void
    {
        static::assertEquals(Action::createBorder(), $this->getBatch()->getAction());
    }

    public function testAddTransaction(): void
    {
        $batch = $this->getBatch();
        $transaction = TransactionMockBuilder::createTransaction('/default.png');
        $transaction->start();
        $tid = $transaction->getId();

        $batch->addTransaction($transaction);

        static::assertEquals($transaction, $batch->getTransactions()[$tid]);
        static::assertEquals($transaction, $batch->getTransaction($tid));
    }

    public function testAddResult(): void
    {
        $transaction = TransactionMockBuilder::createTransaction();
        $batch = $this->getBatch();
        $batch->addTransaction($transaction);
        $thumbnail = $this->createMock(ThumbnailInterface::class);
        $batch->addResult($transaction, $thumbnail);
        $results = $batch->getResults();

        static::assertEquals($thumbnail, $results[$transaction->getId()]);
    }

    public function testAddResultUnrelated(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Attempted to add an invalid file to a non-existent transaction');

        $batch = $this->getBatch();
        $transaction = TransactionMockBuilder::createTransaction();
        $thumbnail = $this->createMock(ThumbnailInterface::class);
        $batch->addResult($transaction, $thumbnail);
    }

    public function testAddException(): void
    {
        $transaction = TransactionMockBuilder::createTransaction();
        $batch = $this->getBatch();
        $batch->addTransaction($transaction);
        $exception = new Exception();
        $batch->addException($transaction->getId(), $exception);

        static::assertEquals([$transaction->getId() => $exception], $batch->getExceptions());
    }

    public function testAddExceptionUnrelated(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Attempted to add an exception to a non-existent transaction');

        $transaction = TransactionMockBuilder::createTransaction();
        $batch = $this->getBatch();
        $exception = new Exception();
        $batch->addException($transaction->getId(), $exception);
    }

    public function testAddInvalidFile(): void
    {
        $transaction = TransactionMockBuilder::createTransaction();
        $batch = $this->getBatch();
        $batch->addTransaction($transaction);
        $tid = $transaction->getId();
        $invalidFile = $this->createMock(FileInterface::class);
        $batch->addInvalidFile($transaction->getId(), $invalidFile);

        static::assertEquals([$tid => $invalidFile], $batch->getInvalidFiles());
    }

    public function testAddInvalidFileUnrelated(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Attempted to add an invalid file to a non-existent transaction');

        $transaction = TransactionMockBuilder::createTransaction();
        $batch = $this->getBatch();
        $invalidFile = $this->createMock(FileInterface::class);
        $batch->addInvalidFile($transaction->getId(), $invalidFile);
    }

    private function getBatch(): Batch
    {
        return new Batch($this->files, 22, 44, Action::createBorder());
    }
}
