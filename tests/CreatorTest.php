<?php

declare(strict_types=1);

namespace Camelot\ImageAssets\Tests;

use Camelot\Filesystem\Adapter\Local;
use Camelot\Filesystem\Filesystem;
use Camelot\Filesystem\Handler\Image;
use Camelot\Filesystem\Handler\Image\Dimensions;
use Camelot\ImageAssets\Creator;
use Camelot\ImageAssets\Image\Action;
use Camelot\ImageAssets\Transaction;
use PHPUnit\Framework\TestCase;
use function is_string;

class CreatorTest extends TestCase
{
    /** @var Filesystem */
    protected $fs;

    /** @var Image */
    protected $logoJpg;
    /** @var Image 1000x667 */
    protected $landscapeImage;
    /** @var Image 427x640 */
    protected $portraitImage;
    /** @var Image */
    protected $svgImage;

    public function setup(): void
    {
        $this->fs = new Filesystem(new Local(__DIR__ . '/images'));
        $this->logoJpg = $this->fs->getImage('generic-logo.jpg');
        $this->landscapeImage = $this->fs->getImage('samples/sample1.jpg');
        $this->portraitImage = $this->fs->getImage('samples/sample2.jpg');
        $this->svgImage = $this->fs->getImage('samples/nut.svg');
    }

    /**
     * @testdox When target dimensions are (0, 0), thumbnail dimensions are set to image dimensions
     */
    public function testFallbacksForAutoscale(): void
    {
        $transaction = new Transaction($this->portraitImage);

        $result = (new Creator())->create($transaction);

        $this->assertDimensions(new Dimensions(427, 640), $result);
    }

    /**
     * @testdox When target width is 0, thumbnail width is autoscaled based on image ratio
     */
    public function testFallbacksForHorizontalAutoscale(): void
    {
        $transaction = new Transaction($this->portraitImage, Action::CROP, new Dimensions(0, 320));

        $result = (new Creator())->create($transaction);

        $this->assertDimensions(new Dimensions(214, 320), $result);
    }

    /**
     * @testdox When target height is 0, thumbnail height is autoscaled based on image ratio
     */
    public function testFallbacksForVerticalAutoscale(): void
    {
        $transaction = new Transaction($this->landscapeImage, Action::CROP, new Dimensions(500, 0));

        $result = (new Creator())->create($transaction);

        $this->assertDimensions(new Dimensions(500, 334), $result);
    }

    /**
     * @testdox When upscaling is allowed, thumbnail is enlarged to target dimensions
     */
    public function testAllowUpscaling(): void
    {
        $upscaled = new Dimensions(800, 600);
        $transaction = new Transaction($this->logoJpg, Action::CROP, $upscaled);

        $result = (new Creator(false))->create($transaction);

        $this->assertDimensions($upscaled, $result);
    }

    /**
     * @testdox When upscaling is not allowed, target dimensions are reduced to current image dimensions
     */
    public function testLimitUpscaling(): void
    {
        $upscaled = new Dimensions(800, 600);
        $original = new Dimensions(624, 351);

        $transaction = new Transaction($this->logoJpg, Action::CROP, $upscaled);

        $result = (new Creator(true))->create($transaction);

        $this->assertDimensions($original, $result);
    }

    public function testLandscapeCrop(): void
    {
        $expected = new Dimensions(500, 200);
        $transaction = new Transaction($this->landscapeImage, Action::CROP, $expected);

        $result = (new Creator())->create($transaction);

        $this->assertDimensions($expected, $result);
    }

    public function testLandscapeResize(): void
    {
        $transaction = new Transaction($this->landscapeImage, Action::RESIZE, new Dimensions(500, 200));

        $result = (new Creator())->create($transaction);

        $this->assertDimensions(new Dimensions(299, 200), $result);
    }

    public function testLandscapeFit(): void
    {
        $expected = new Dimensions(500, 200);
        $transaction = new Transaction($this->landscapeImage, Action::FIT, $expected);

        $result = (new Creator())->create($transaction);

        $this->assertDimensions($expected, $result);
    }

    public function testLandscapeBorder(): void
    {
        $expected = new Dimensions(500, 200);
        $transaction = new Transaction($this->landscapeImage, Action::BORDER, $expected);

        $result = (new Creator())->create($transaction);

        $this->assertDimensions($expected, $result);
    }

    public function testPortraitCrop(): void
    {
        $expected = new Dimensions(200, 500);
        $transaction = new Transaction($this->portraitImage, Action::CROP, $expected);

        $result = (new Creator())->create($transaction);

        $this->assertDimensions($expected, $result);
    }

    public function testPortraitResize(): void
    {
        $transaction = new Transaction($this->portraitImage, Action::RESIZE, new Dimensions(200, 500));

        $result = (new Creator())->create($transaction);

        $this->assertDimensions(new Dimensions(200, 299), $result);
    }

    public function testPortraitFit(): void
    {
        $expected = new Dimensions(200, 500);
        $transaction = new Transaction($this->portraitImage, Action::FIT, $expected);

        $result = (new Creator())->create($transaction);

        $this->assertDimensions($expected, $result);
    }

    public function testPortraitBorder(): void
    {
        $expected = new Dimensions(200, 500);
        $transaction = new Transaction($this->portraitImage, Action::BORDER, $expected);

        $result = (new Creator())->create($transaction);

        $this->assertDimensions($expected, $result);
    }

    public function testSvg(): void
    {
        $transaction = new Transaction($this->portraitImage, Action::RESIZE, new Dimensions(200, 500));

        $result = (new Creator())->create($transaction);

        $this->assertDimensions(new Dimensions(200, 299), $result);
    }

    /**
     * @param Dimensions|string $actual
     */
    protected function assertDimensions(Dimensions $expected, $actual): void
    {
        if (is_string($actual)) {
            $info = Image\Info::createFromString($actual);
            $actual = new Dimensions($info->getWidth(), $info->getHeight());
        }
        $this->assertEquals($expected, $actual, "Expected dimension $expected does not equal actual $actual");
    }
}
