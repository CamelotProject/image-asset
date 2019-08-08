<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Thumbnail;

use Camelot\ImageAsset\Thumbnail\Watermarker;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Thumbnail\Watermarker
 */
final class WatermarkerTest extends TestCase
{
    public function testWatermark(): void
    {
        static::assertSame('test.png', $this->getWatermarker()->watermark('test.png', 'overlay.png'));
    }

    private function getWatermarker(): Watermarker
    {
        return new Watermarker();
    }
}
