<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Functional;

use Camelot\ImageAsset\Tests\Fixtures\App\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as FrameworkBundleKernelTestCase;

abstract class KernelTestCase extends FrameworkBundleKernelTestCase
{
    protected static function getKernelClass()
    {
        return TestKernel::class;
    }
}
