<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Transaction;

use Camelot\ImageAsset\Exception\BadMethodCallException;
use Camelot\ImageAsset\Exception\ExceptionInterface;
use Camelot\ImageAsset\Exception\RuntimeException;
use Camelot\ImageAsset\Filesystem\FilesystemInterface;
use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Thumbnail\CreatorInterface;
use Camelot\ImageAsset\Thumbnail\NameGenerator;
use Camelot\ImageAsset\Thumbnail\Thumbnail;
use Camelot\ImageAsset\Thumbnail\ThumbnailInterface;
use Throwable;

/**
 * Responder is responsible for processing the transaction.
 * It invokes the finder and creator, and handles the caching logic.
 */
final class Processor implements ProcessorInterface
{
    /** @var CreatorInterface */
    private $creator;
    /** @var NameGenerator */
    private $nameGenerator;
    /** @var FilesystemInterface */
    private $targetFilesystem;

    public function __construct(CreatorInterface $creator, NameGenerator $nameGenerator, FilesystemInterface $targetFilesystem)
    {
        $this->creator = $creator;
        $this->nameGenerator = $nameGenerator;
        $this->targetFilesystem = $targetFilesystem;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ExceptionInterface
     */
    public function process(Transaction $transaction, ?FilesystemInterface $targetFilesystem = null): ThumbnailInterface
    {
        if ($transaction->isComplete()) {
            throw new BadMethodCallException('Cannot process a completed transaction.');
        }

        try {
            $transaction->start();
            $thumbnail = $this->doProcess($transaction->getCurrent(), $targetFilesystem);
        } catch (Throwable $e) {
            $transaction->setResult(Transaction::FAIL);

            throw $e instanceof ExceptionInterface ? $e : new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
        $transaction->setResult(Transaction::PASS);

        return $thumbnail;
    }

    private function doProcess(JobInterface $job, FilesystemInterface $targetFilesystem = null): ThumbnailInterface
    {
        $targetFilesystem = $targetFilesystem ?: $this->targetFilesystem;
        $thumbnailPath = $this->nameGenerator->generateFromJob($job);
        if ($targetFilesystem->exists($thumbnailPath)) {
            $image = $targetFilesystem->get($thumbnailPath);
            $thumbnailData = $image->read();
        } else {
            $thumbnailData = $this->creator->create($job);
            $image = $targetFilesystem->write($thumbnailPath, $thumbnailData);
        }

        /** @var ImageInterface $image */
        return new Thumbnail($image, $thumbnailData);
    }
}
