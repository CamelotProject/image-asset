<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Thumbnail;

use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Attributes\Color;
use Camelot\ImageAsset\Image\Dimensions;
use Camelot\ImageAsset\Tests\Fixtures\Transaction\TransactionMockBuilder;
use Camelot\ImageAsset\Tests\ThumbnailAssertTrait;
use Camelot\ImageAsset\Thumbnail\Resizer;
use Contao\ImagineSvg\Imagine as SvgImagine;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Thumbnail\Resizer
 */
final class ResizerTest extends TestCase
{
    use ThumbnailAssertTrait;

    public function testLandscapeBorder(): void
    {
        $expected = new Dimensions(500, 200);
        $job = TransactionMockBuilder::createTransaction('/landscape.png', Action::createBorder(), 500, 200)->start();
        $this->getResizer()->resize($job);

        $this->assertDimensions($expected, $job->getTargetDimensions());
    }

    public function testLandscapeCrop(): void
    {
        $expected = new Dimensions(500, 200);
        $job = TransactionMockBuilder::createTransaction('/landscape.png', Action::createCrop(), 500, 200)->start();
        $this->getResizer()->resize($job);

        $this->assertDimensions($expected, $job->getTargetDimensions());
    }

    public function testLandscapeFit(): void
    {
        $expected = new Dimensions(500, 200);
        $job = TransactionMockBuilder::createTransaction('/landscape.png', Action::createFit(), 500, 200)->start();
        $this->getResizer()->resize($job);

        $this->assertDimensions($expected, $job->getTargetDimensions());
    }

    public function testLandscapeResize(): void
    {
        $expected = new Dimensions(500, 200);
        $job = TransactionMockBuilder::createTransaction('/landscape.png', Action::createResize(), 500, 200)->start();
        $this->getResizer()->resize($job);

        $this->assertDimensions($expected, $job->getTargetDimensions());
    }

    public function testPortraitBorder(): void
    {
        $expected = new Dimensions(200, 500);
        $job = TransactionMockBuilder::createTransaction('/portrait.png', Action::createBorder(), 200, 500)->start();
        $this->getResizer()->resize($job);

        $this->assertDimensions($expected, $job->getTargetDimensions());
    }

    public function testPortraitCrop(): void
    {
        $expected = new Dimensions(200, 500);
        $job = TransactionMockBuilder::createTransaction('/portrait.png', Action::createCrop(), 200, 500)->start();
        $this->getResizer()->resize($job);

        $this->assertDimensions($expected, $job->getTargetDimensions());
    }

    public function testPortraitFit(): void
    {
        $expected = new Dimensions(200, 500);
        $job = TransactionMockBuilder::createTransaction('/portrait.png', Action::createFit(), 200, 500)->start();
        $this->getResizer()->resize($job);

        $this->assertDimensions($expected, $job->getTargetDimensions());
    }

    public function testPortraitResize(): void
    {
        $expected = new Dimensions(200, 500);
        $job = TransactionMockBuilder::createTransaction('/portrait.png', Action::createResize(), 200, 500)->start();
        $this->getResizer()->resize($job);

        $this->assertDimensions($expected, $job->getTargetDimensions());
    }

    public function testSvg(): void
    {
        $expected = new Dimensions(200, 500);
        $job = TransactionMockBuilder::createTransaction('/svg.svg', Action::createResize(), 200, 500)->start();
        $result = $this->getResizer()->resizeSvg($job);

        static::assertIsString($result);
        $this->assertDimensions($expected, $job->getTargetDimensions());
    }

    private function getResizer(): Resizer
    {
        return new Resizer(new SvgImagine(), Color::white());
    }
}
