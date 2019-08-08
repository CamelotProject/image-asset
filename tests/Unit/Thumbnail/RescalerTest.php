<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Thumbnail;

use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Dimensions;
use Camelot\ImageAsset\Tests\Fixtures\Transaction\TransactionMockBuilder;
use Camelot\ImageAsset\Tests\ThumbnailAssertTrait;
use Camelot\ImageAsset\Thumbnail\Rescaler;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Thumbnail\Rescaler
 */
final class RescalerTest extends TestCase
{
    use ThumbnailAssertTrait;

    public function providerCreate(): iterable
    {
        yield ['placeholder.jpg', 1200, 1200];
        yield ['landscape.png', 1000, 667];
        yield ['svg.svg', 1000, 531];
    }

    /**
     * @testdox When target dimensions are (0, 0), thumbnail dimensions are set to image dimensions
     */
    public function testFallbacksForAutoscale(): void
    {
        $job = TransactionMockBuilder::createTransaction('/default.png', Action::createCrop(), 0, 0)->start();
        $this->getRescaler()->autoscale($job);

        $this->assertDimensions(new Dimensions(1200, 1200), $job->getTargetDimensions());
    }

    /**
     * @testdox When target width is 0, thumbnail width is autoscaled based on image ratio
     */
    public function testFallbacksForHorizontalAutoscale(): void
    {
        $job = TransactionMockBuilder::createTransaction('/default.png', Action::createCrop(), 0, 320)->start();
        $this->getRescaler()->autoscale($job);

        $this->assertDimensions(new Dimensions(320, 320), $job->getTargetDimensions());
    }

    /**
     * @testdox When target height is 0, thumbnail height is autoscaled based on image ratio
     */
    public function testFallbacksForVerticalAutoscale(): void
    {
        $job = TransactionMockBuilder::createTransaction('/default.png', Action::createCrop(), 500, 0)->start();
        $this->getRescaler()->autoscale($job);

        $this->assertDimensions(new Dimensions(500, 500), $job->getTargetDimensions());
    }

    /**
     * @testdox When upscaling is allowed, thumbnail is enlarged to target dimensions
     */
    public function testAllowUpscaling(): void
    {
        $expected = new Dimensions(1400, 1400);
        $job = TransactionMockBuilder::createTransaction('/default.png', Action::createCrop(), 1400, 1400)->start();
        $this->getRescaler(false)->autoscale($job);

        $this->assertDimensions($expected, $job->getTargetDimensions());
    }

    /**
     * @testdox When upscaling is not allowed, target dimensions are reduced to current image dimensions
     */
    public function testLimitUpscaling(): void
    {
        $expected = new Dimensions(1200, 1200);
        $job = TransactionMockBuilder::createTransaction('/default.png', Action::createCrop(), 1400, 1400)->start();
        $this->getRescaler()->autoscale($job);

        $this->assertDimensions($expected, $job->getTargetDimensions());
    }

    public function testLandscapeBorder(): void
    {
        $expected = new Dimensions(500, 200);
        $job = TransactionMockBuilder::createTransaction('/landscape.png', Action::createBorder(), 500, 200)->start();
        $this->getRescaler()->autoscale($job);

        $this->assertDimensions($expected, $job->getTargetDimensions());
    }

    public function testLandscapeCrop(): void
    {
        $expected = new Dimensions(500, 200);
        $job = TransactionMockBuilder::createTransaction('/landscape.png', Action::createCrop(), 500, 200)->start();
        $this->getRescaler()->autoscale($job);

        $this->assertDimensions($expected, $job->getTargetDimensions());
    }

    public function testLandscapeFit(): void
    {
        $expected = new Dimensions(500, 200);
        $job = TransactionMockBuilder::createTransaction('/landscape.png', Action::createFit(), 500, 200)->start();
        $this->getRescaler()->autoscale($job);

        $this->assertDimensions($expected, $job->getTargetDimensions());
    }

    public function testLandscapeResize(): void
    {
        $expected = new Dimensions(500, 200);
        $job = TransactionMockBuilder::createTransaction('/landscape.png', Action::createResize(), 500, 200)->start();
        $this->getRescaler()->autoscale($job);

        $this->assertDimensions($expected, $job->getTargetDimensions());
    }

    public function testPortraitBorder(): void
    {
        $expected = new Dimensions(200, 500);
        $job = TransactionMockBuilder::createTransaction('/portrait.png', Action::createBorder(), 200, 500)->start();
        $this->getRescaler()->autoscale($job);

        $this->assertDimensions($expected, $job->getTargetDimensions());
    }

    public function testPortraitCrop(): void
    {
        $expected = new Dimensions(200, 500);
        $job = TransactionMockBuilder::createTransaction('/portrait.png', Action::createCrop(), 200, 500)->start();
        $this->getRescaler()->autoscale($job);

        $this->assertDimensions($expected, $job->getTargetDimensions());
    }

    public function testPortraitFit(): void
    {
        $expected = new Dimensions(200, 500);
        $job = TransactionMockBuilder::createTransaction('/portrait.png', Action::createFit(), 200, 500)->start();
        $this->getRescaler()->autoscale($job);

        $this->assertDimensions($expected, $job->getTargetDimensions());
    }

    public function testPortraitResize(): void
    {
        $expected = new Dimensions(200, 500);
        $job = TransactionMockBuilder::createTransaction('/portrait.png', Action::createResize(), 200, 500)->start();
        $this->getRescaler()->autoscale($job);

        $this->assertDimensions($expected, $job->getTargetDimensions());
    }

    public function testSvg(): void
    {
        $job = TransactionMockBuilder::createTransaction('/svg.svg', Action::createResize(), 200, 500)->start();
        $this->getRescaler()->autoscale($job);

        $this->assertDimensions(new Dimensions(200, 500), $job->getTargetDimensions());
    }

    private function getRescaler(bool $limitUpscalling = true): Rescaler
    {
        return new Rescaler($limitUpscalling);
    }
}
