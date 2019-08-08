<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Transaction;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Transaction\Job
 * @covers \Camelot\ImageAsset\Transaction\PhaseTrait
 */
final class JobTest extends TestCase
{
    use PhaseTestTrait;
}
