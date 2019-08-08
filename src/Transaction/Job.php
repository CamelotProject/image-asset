<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Transaction;

final class Job implements JobInterface
{
    use PhaseTrait;

    /** @codeCoverageIgnore */
    private function __construct()
    {
    }
}
