<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Functional;

use Camelot\ImageAsset\Tests\Fixtures\App\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as FrameworkWebTestCaseTestCase;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

abstract class WebTestCase extends FrameworkWebTestCaseTestCase
{
    protected static function getKernelClass()
    {
        return TestKernel::class;
    }

    protected function assertHttpStatusCode(int $expected, SymfonyResponse $response): void
    {
        if ($expected !== $response->getStatusCode()) {
            static::fail("Incorrect HTTP status code. Expected $expected, got {$response->getStatusCode()}");
        } else {
            $this->addToAssertionCount(1);
        }
    }
}
