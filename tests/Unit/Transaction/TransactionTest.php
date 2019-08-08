<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Transaction;

use Camelot\ImageAsset\Exception\BadMethodCallException;
use Camelot\ImageAsset\Exception\RuntimeException;
use Camelot\ImageAsset\Tests\Fixtures\Transaction\TransactionMockBuilder;
use Camelot\ImageAsset\Transaction\JobInterface;
use Camelot\ImageAsset\Transaction\Requisition;
use Camelot\ImageAsset\Transaction\RequisitionInterface;
use Camelot\ImageAsset\Transaction\Transaction;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Camelot\ImageAsset\Transaction\Transaction
 */
final class TransactionTest extends TestCase
{
    public function testConstruct(): Transaction
    {
        $transaction = TransactionMockBuilder::createTransaction();

        static::assertInstanceOf(Transaction::class, $transaction);

        return $transaction;
    }

    /**
     * @depends testConstruct
     */
    public function testGetId(): void
    {
        static::assertNotEmpty(TransactionMockBuilder::createTransaction()->getId());
    }

    public function providerPassFail(): iterable
    {
        yield 'PASS' => [true, Transaction::PASS];
        yield 'FAIL' => [false, Transaction::FAIL];
    }

    /**
     * @depends      testConstruct
     * @dataProvider providerPassFail
     */
    public function testPassFail(bool $expected, int $result): void
    {
        $transaction = TransactionMockBuilder::createTransaction();

        static::assertFalse($transaction->isComplete());
        $transaction->setResult($result);
        static::assertTrue($transaction->isComplete());
        static::assertSame($expected, $transaction->isPass());
        static::assertSame(!$expected, $transaction->isFail());
    }

    /**
     * @depends testConstruct
     */
    public function testGetRequisition(): void
    {
        $transaction = TransactionMockBuilder::createTransaction();
        static::assertFalse($transaction->isComplete());
        static::assertInstanceOf(RequisitionInterface::class, $transaction->getCurrent());
    }

    /**
     * @depends testConstruct
     */
    public function testGetJob(): void
    {
        $transaction = TransactionMockBuilder::createTransaction();
        static::assertFalse($transaction->isComplete());
        static::assertInstanceOf(JobInterface::class, $transaction->start());
    }

    /**
     * @depends testConstruct
     */
    public function testSetResult(): void
    {
        $transaction = TransactionMockBuilder::createTransaction();
        static::assertFalse($transaction->isComplete(), 'Un-started transaction is reporting being complete');
        $transaction->start();
        static::assertFalse($transaction->isComplete(), 'Freshly started transaction is reporting being complete');
        $transaction->setResult(Transaction::PASS);
        static::assertTrue($transaction->isComplete(), 'Completed transaction is reporting NOT being complete');
    }

    public function testSetResultInvalid(): void
    {
        $this->expectException(BadMethodCallException::class);

        $transaction = TransactionMockBuilder::createTransaction();
        $transaction->setResult(1234567890);
    }

    /**
     * @depends testConstruct
     */
    public function testGetJobLate(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot start an already started transaction');

        $transaction = TransactionMockBuilder::createTransaction();
        $transaction->getCurrent();
        $transaction->start();
        $transaction->setResult(Transaction::PASS);
        $transaction->start();
    }

    public function providerBadCallable(): iterable
    {
        yield 'Invalid type' => [function () { return 44; }, 'integer'];
        yield 'Invalid interface' => [function () { return new stdClass(); }, stdClass::class];
        yield 'Requisition instead of job' => [function () { return Requisition::create(); }, Requisition::class];
    }

    /** @dataProvider providerBadCallable */
    public function testBadCallable(callable $job, string $type): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp('#Callables used to construct jobs must return objects implementing Camelot.ImageAsset.Transaction.JobInterface, ' . str_replace('\\', '.', $type) . ' given#');

        $transaction = TransactionMockBuilder::createTransactionWithCallable($job);
        $transaction->start();
    }
}
