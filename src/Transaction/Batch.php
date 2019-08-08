<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Transaction;

use Camelot\ImageAsset\Exception\BadMethodCallException;
use Camelot\ImageAsset\Filesystem\FileInterface;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Thumbnail\ThumbnailInterface;
use Throwable;

final class Batch
{
    /** @var iterable */
    private $files;
    /** @var int */
    private $width;
    /** @var int */
    private $height;
    /** @var Action */
    private $action;
    /** @var Transaction[] */
    private $transactions = [];
    /** @var iterable */
    private $results = [];
    /** @var iterable */
    private $invalidFiles = [];
    /** @var iterable */
    private $exceptions = [];

    public function __construct(iterable $files, int $width, int $height, Action $action)
    {
        $this->files = $files;
        $this->width = $width;
        $this->height = $height;
        $this->action = $action;
    }

    public function getFiles(): iterable
    {
        return $this->files;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getAction(): Action
    {
        return $this->action;
    }

    public function addTransaction(Transaction $transaction): self
    {
        $this->transactions[$transaction->getId()] = $transaction;

        return $this;
    }

    public function getTransaction(string $transactionId): Transaction
    {
        return $this->transactions[$transactionId];
    }

    public function getTransactions(): iterable
    {
        return $this->transactions;
    }

    public function addResult(Transaction $transaction, ThumbnailInterface $result): self
    {
        if (!isset($this->transactions[$transaction->getId()])) {
            throw new BadMethodCallException(sprintf('Attempted to add an invalid file to a non-existent transaction: %s', $transaction->getId()));
        }
        $this->results[$transaction->getId()] = $result;

        return $this;
    }

    /** @return ThumbnailInterface[] */
    public function getResults(): iterable
    {
        return $this->results;
    }

    public function addInvalidFile(string $transactionId, FileInterface $invalidFile): self
    {
        $transaction = $this->transactions[$transactionId] ?? null;
        if ($transaction === null) {
            throw new BadMethodCallException(sprintf('Attempted to add an invalid file to a non-existent transaction: %s', $transactionId));
        }
        $transaction->setResult(Transaction::FAIL);
        $this->invalidFiles[$transactionId] = $invalidFile;

        return $this;
    }

    public function getInvalidFiles(): iterable
    {
        return $this->invalidFiles;
    }

    public function addException(string $transactionId, Throwable $exception): self
    {
        $transaction = $this->transactions[$transactionId] ?? null;
        if ($transaction === null) {
            throw new BadMethodCallException(sprintf('Attempted to add an exception to a non-existent transaction: %s', $transactionId));
        }
        $transaction->setResult(Transaction::FAIL);
        $this->exceptions[$transaction->getId()] = $exception;

        return $this;
    }

    public function getExceptions(): iterable
    {
        return $this->exceptions;
    }
}
