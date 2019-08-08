<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Exception;

use Camelot\ImageAsset\Exception\NotFoundHttpException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Exception\NotFoundHttpException
 */
final class NotFoundHttpExceptionTest extends TestCase
{
    public function testGetMessage(): void
    {
        static::assertStringContainsString(
            'There was an error with the thumbnail image requested',
            (new NotFoundHttpException('asset.jpg'))->getMessage()
        );
    }
}
