<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Command\Helper;

use Camelot\ImageAsset\Command\Helper\BatchResultTable;
use Camelot\ImageAsset\Exception\RuntimeException;
use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use Camelot\ImageAsset\Tests\Fixtures\Transaction\TransactionMockBuilder;
use Camelot\ImageAsset\Thumbnail\Thumbnail;
use Camelot\ImageAsset\Transaction\Batch;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @covers \Camelot\ImageAsset\Command\Helper\BatchResultTable
 */
final class BatchResultTableTest extends TestCase
{
    /** @var BufferedOutput */
    private $bufferedOutput;

    protected function setUp(): void
    {
        $this->bufferedOutput = new BufferedOutput();
    }

    public function providerBatch(): iterable
    {
        $transaction = TransactionMockBuilder::createTransaction();
        $id = $transaction->getId();
        $batch = new Batch([], 1, 2, Action::createCrop());

        $builder = FilesystemMockBuilder::create();
        /** @var ImageInterface $default */
        $default = $builder->createImages()->get('default.png');
        /** @var ImageInterface $error */
        $error = $builder->createImages()->get('error.png');
        $batch
            ->addTransaction($transaction)
            ->addResult($transaction, new Thumbnail($default, $default->read()))
            ->addInvalidFile($id, $error)
            ->addException($id, new RuntimeException('File was not much good'))
        ;

        yield [$batch, '#Result.+Source.+Output file#'];
        yield [$batch, '#PASS.+default.png.+default.png#'];
        yield [$batch, '#FAIL.+error.png#'];
        yield [$batch, '#ERROR.+File was not much good#'];
    }

    /** @dataProvider providerBatch */
    public function testOutput(Batch $batch, string $regExp): void
    {
        $helper = $this->getBatchResultTable();
        $helper->output($batch);

        static::assertRegExp($regExp, $this->bufferedOutput->fetch());
    }

    private function getBatchResultTable(): BatchResultTable
    {
        return new BatchResultTable(new ArrayInput([]), $this->bufferedOutput);
    }
}
