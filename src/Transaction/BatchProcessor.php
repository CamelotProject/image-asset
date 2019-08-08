<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Transaction;

use Camelot\ImageAsset\Exception\UnsupportedFileTypeException;
use Camelot\ImageAsset\Filesystem\FileInterface;
use Camelot\ImageAsset\Filesystem\FilesystemInterface;
use Camelot\ImageAsset\Filesystem\FinderInterface;
use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Dimensions;

final class BatchProcessor
{
    /** @var TransactionBuilder */
    private $transactionBuilder;
    /** @var ProcessorInterface */
    private $processor;

    public function __construct(TransactionBuilder $transactionBuilder, ProcessorInterface $processor)
    {
        $this->transactionBuilder = $transactionBuilder;
        $this->processor = $processor;
    }

    public function createBatch(FinderInterface $files, int $width, int $height, string $action): Batch
    {
        $batch = new Batch($files, $width, $height, Action::create($action));
        $dimensions = ($width !== null || $height !== null) ? new Dimensions($width ?: 0, $height ?: 0) : null;
        /** @var FileInterface $file */
        foreach ($files as $file) {
            $transaction = $this->transactionBuilder->createTransaction();
            $batch->addTransaction($transaction);

            if (!$file instanceof ImageInterface) {
                $batch->addInvalidFile($transaction->getId(), $file);

                continue;
            }

            /** @var RequisitionInterface $requisition */
            $requisition = $transaction->getCurrent();
            $requisition
                ->setRequestPath($file->getRelativePathname())
                ->setAction(Action::create($action))
                ->setTargetDimensions($dimensions)
                ->setRequestImage($file)
            ;
        }

        return $batch;
    }

    public function process(Batch $batch, FilesystemInterface $targetFilesystem = null): void
    {
        /** @var Transaction $transaction */
        foreach ($batch->getTransactions() as $transaction) {
            if ($transaction->isComplete()) {
                continue;
            }

            try {
                $batch->addResult($transaction, $this->processor->process($transaction, $targetFilesystem));
            } catch (UnsupportedFileTypeException $e) {
                $batch->addException($transaction->getId(), $e);
            }
        }
    }
}
