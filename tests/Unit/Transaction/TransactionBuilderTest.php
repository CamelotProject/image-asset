<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Transaction;

use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Dimensions;
use Camelot\ImageAsset\Tests\Fixtures\Transaction\TransactionMockBuilder;
use Camelot\ImageAsset\Transaction\JobInterface;
use Camelot\ImageAsset\Transaction\PhaseInterface;
use Camelot\ImageAsset\Transaction\RequisitionInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Transaction\TransactionBuilder
 */
final class TransactionBuilderTest extends TestCase
{
    public function providerTransaction(): iterable
    {
        $action = Action::createCrop();
        $dimensions = new Dimensions(400, 300);
        $image = $this->createMock(ImageInterface::class);

        yield ['/default.png', $action, $dimensions, $image];
    }

    /** @dataProvider providerTransaction */
    public function testCreateRequisition(string $requestPath, Action $action, Dimensions $dimensions, ImageInterface $image): void
    {
        $transaction = TransactionMockBuilder::createTransaction($requestPath, $action, $dimensions->getWidth(), $dimensions->getHeight());
        /** @var RequisitionInterface $requisition */
        $requisition = $transaction->getCurrent();
        $requisition->setRequestImage($image);

        $this->assertFoo($requisition, RequisitionInterface::class, $requestPath, $action, $dimensions, $image);
    }

    /** @dataProvider providerTransaction */
    public function testCreateRequisitionFluent(string $requestPath, Action $action, Dimensions $dimensions, ImageInterface $image): void
    {
        $transaction = TransactionMockBuilder::createTransaction();
        /** @var RequisitionInterface $requisition */
        $requisition = $transaction->getCurrent();
        $requisition
            ->setRequestPath($requestPath)
            ->setAction($action)
            ->setTargetDimensions($dimensions)
            ->setRequestImage($image)
        ;

        $this->assertFoo($requisition, RequisitionInterface::class, $requestPath, $action, $dimensions, $image);
    }

    /** @dataProvider providerTransaction */
    public function testCreateJob(string $requestPath, Action $action, Dimensions $dimensions, ImageInterface $image): void
    {
        $transaction = TransactionMockBuilder::createTransaction($requestPath, $action, $dimensions->getWidth(), $dimensions->getHeight());
        /** @var RequisitionInterface $requisition */
        $requisition = $transaction->getCurrent();
        $requisition->setRequestImage($image);

        $this->assertFoo($transaction->start(), JobInterface::class, $requestPath, $action, $dimensions, $image);
    }

    /** @dataProvider providerTransaction */
    public function testCreateFromJob(string $requestPath, Action $action, Dimensions $dimensions, ImageInterface $image): void
    {
        $builder = TransactionMockBuilder::createBuilder();
        $transaction = TransactionMockBuilder::createTransaction($requestPath, $action, $dimensions->getWidth(), $dimensions->getHeight());
        /** @var RequisitionInterface $requisition */
        $requisition = $transaction->getCurrent();
        $requisition->setRequestImage($image);
        $job = $transaction->start();

        $newTransaction = $builder->createFromJob($job);

        $this->assertFoo($newTransaction->getCurrent(), RequisitionInterface::class, $requestPath, $action, $dimensions, $image);
    }

    private function assertFoo(PhaseInterface $phase, string $expectedInstance, string $requestPath, Action $action, Dimensions $dimensions, ImageInterface $image): void
    {
        static::assertInstanceOf($expectedInstance, $phase);
        static::assertSame($requestPath, $phase->getRequestPath());
        static::assertSame($image, $phase->getRequestImage());
        static::assertEquals($action, $phase->getAction());
        static::assertEquals($dimensions, $phase->getTargetDimensions());
    }
}
