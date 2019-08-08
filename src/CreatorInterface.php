<?php

declare(strict_types=1);

namespace Camelot\ImageAssets;

interface CreatorInterface
{
    /**
     * Creates a thumbnail for the given transaction.
     *
     *
     * @return string thumbnail data
     */
    public function create(Transaction $transaction);
}
