<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Thumbnail;

use Camelot\ImageAsset\Transaction\JobInterface;

interface CreatorInterface
{
    /** Creates a thumbnail for the given transaction. */
    public function create(JobInterface $job): ?string;
}
