<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Transaction;

use Camelot\ImageAsset\Filesystem\FilesystemInterface;
use Camelot\ImageAsset\Thumbnail\ThumbnailInterface;

interface ProcessorInterface
{
    /** Process the transaction and return a thumbnail. */
    public function process(Transaction $transaction, ?FilesystemInterface $targetFilesystem = null): ThumbnailInterface;
}
