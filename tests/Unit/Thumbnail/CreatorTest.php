<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Thumbnail;

use Camelot\ImageAsset\Exception\InvalidImageException;
use Camelot\ImageAsset\Exception\NotFoundHttpException;
use Camelot\ImageAsset\Filesystem\Image;
use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Attributes\Color;
use Camelot\ImageAsset\Image\Attributes\Info;
use Camelot\ImageAsset\Image\Dimensions;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use Camelot\ImageAsset\Tests\Fixtures\Transaction\TransactionMockBuilder;
use Camelot\ImageAsset\Tests\ThumbnailAssertTrait;
use Camelot\ImageAsset\Thumbnail\Creator;
use Camelot\ImageAsset\Thumbnail\Rescaler;
use Camelot\ImageAsset\Thumbnail\Resizer;
use Camelot\ImageAsset\Transaction\JobInterface;
use Contao\ImagineSvg\Imagine as SvgImagine;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @covers \Camelot\ImageAsset\Thumbnail\Creator
 */
final class CreatorTest extends TestCase
{
    use ThumbnailAssertTrait;

    public function providerCreate(): iterable
    {
        yield ['placeholder.jpg', 1200, 1200];
        yield ['landscape.png', 1000, 667];
        yield ['svg.svg', 1000, 531];
    }

    /** @dataProvider providerCreate */
    public function testCreate(string $fileName, int $width, int $height): void
    {
        $transaction = TransactionMockBuilder::createTransaction($fileName, Action::createCrop(), $width, $height);
        $job = $transaction->start();

        $result = $this->getCreator()->create($job);

        $this->assertDimensions(new Dimensions($width, $height), $result);
    }

    public function testCreateNotFoundImage(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('There was an error with the thumbnail image requested: /default.png');

        $job = $this->createConfiguredMock(JobInterface::class, [
            'getRequestPath' => '/default.png',
            'getAction' => Action::createCrop(),
            'getTargetDimensions' => new Dimensions(400, 300),
            'getRequestFilePath' => 'default.png',
            'getRequestImage' => new Image(FilesystemMockBuilder::create()->createScratch(), 'image.png'),
        ]);

        $this->getCreator()->create($job);
    }

    public function testCreateInvalidImage(): void
    {
        $this->expectException(InvalidImageException::class);
        $this->expectExceptionMessage('Image file default.png is invalid');

        $image = $this->createConfiguredMock(ImageInterface::class, [
            'exists' => true,
            'getInfo' => Info::createInvalid(),
            'getRelativePathname' => 'default.png',
        ]);
        $job = $this->createConfiguredMock(JobInterface::class, [
            'getRequestPath' => '/default.png',
            'getAction' => Action::createCrop(),
            'getTargetDimensions' => new Dimensions(400, 300),
            'getRequestFilePath' => 'default.png',
            'getRequestImage' => $image,
        ]);

        $this->getCreator()->create($job);
    }

    private function getCreator(): Creator
    {
        return new Creator(new Rescaler(false), new Resizer(new SvgImagine(), Color::white()), new EventDispatcher());
    }
}
