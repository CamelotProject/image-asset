<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Thumbnail;

use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Dimensions;
use Camelot\ImageAsset\Thumbnail\NameGenerator;
use Camelot\ImageAsset\Transaction\JobInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Thumbnail\NameGenerator
 */
final class NameGeneratorTest extends TestCase
{
    public function providerNames(): iterable
    {
        yield 'Filename only pattern' => ['image.png', '{file}', 2048, 2048, Action::createCrop(), 'image.png'];

        yield 'Default Base' => ['11x22/crop/image.png', '{width}x{height}/{action}/{file}', 11, 22, Action::createCrop(), 'image.png'];
        yield 'Default +1 sub' => ['33x44/crop/sub-1/image.png', '{width}x{height}/{action}/{file}', 33, 44, Action::createCrop(), 'sub-1/image.png'];
        yield 'Default +2 subs' => ['55x66/crop/sub-1/sub-2/image.png', '{width}x{height}/{action}/{file}', 55, 66, Action::createCrop(), 'sub-1/sub-2/image.png'];

        yield 'B. Base' => ['11x22-crop/image.png', '{width}x{height}-{action}/{file}', 11, 22, Action::createCrop(), 'image.png'];
        yield 'B. +1 sub' => ['33x44-crop/sub-1/image.png', '{width}x{height}-{action}/{file}', 33, 44, Action::createCrop(), 'sub-1/image.png'];
        yield 'B. +2 subs' => ['55x66-crop/sub-1/sub-2/image.png', '{width}x{height}-{action}/{file}', 55, 66, Action::createCrop(), 'sub-1/sub-2/image.png'];

        yield 'C. Base' => ['11/22/crop/image.png', '{width}/{height}/{action}/{file}', 11, 22, Action::createCrop(), 'image.png'];
        yield 'C. +1 sub' => ['33/44/crop/sub-1/image.png', '{width}/{height}/{action}/{file}', 33, 44, Action::createCrop(), 'sub-1/image.png'];
        yield 'C. +2 subs' => ['55/66/crop/sub-1/sub-2/image.png', '{width}/{height}/{action}/{file}', 55, 66, Action::createCrop(), 'sub-1/sub-2/image.png'];

        yield 'D. Base' => ['crop/11/22/image.png', '{action}/{width}/{height}/{file}', 11, 22, Action::createCrop(), 'image.png'];
        yield 'D. +1 sub' => ['crop/33/44/sub-1/image.png', '{action}/{width}/{height}/{file}', 33, 44, Action::createCrop(), 'sub-1/image.png'];
        yield 'D. +2 subs' => ['crop/55/66/sub-1/sub-2/image.png', '{action}/{width}/{height}/{file}', 55, 66, Action::createCrop(), 'sub-1/sub-2/image.png'];

        yield 'Path prefix slash' => ['11x22/crop/image.png', '/{width}x{height}/{action}/{file}', 11, 22, Action::createCrop(), 'image.png'];
        yield 'Path prefix slash +1 sub' => ['33x44/crop/sub-1/image.png', '/{width}x{height}/{action}/{file}', 33, 44, Action::createCrop(), 'sub-1/image.png'];
        yield 'Path prefix slash +2 subs' => ['55x66/crop/sub-1/sub-2/image.png', '/{width}x{height}/{action}/{file}', 55, 66, Action::createCrop(), 'sub-1/sub-2/image.png'];

        yield 'File path prefix slash' => ['11x22/crop/image.png', '{width}x{height}/{action}/{file}', 11, 22, Action::createCrop(), '/image.png'];
        yield 'File path prefix slash +1 sub' => ['33x44/crop/sub-1/image.png', '{width}x{height}/{action}/{file}', 33, 44, Action::createCrop(), '/sub-1/image.png'];
        yield 'File path prefix slash +2 subs' => ['55x66/crop/sub-1/sub-2/image.png', '{width}x{height}/{action}/{file}', 55, 66, Action::createCrop(), '/sub-1/sub-2/image.png'];

        yield 'Path & file path prefix slash' => ['11x22/crop/image.png', '/{width}x{height}/{action}/{file}', 11, 22, Action::createCrop(), '/image.png'];
        yield 'Path & file path prefix slash +1 sub' => ['33x44/crop/sub-1/image.png', '/{width}x{height}/{action}/{file}', 33, 44, Action::createCrop(), '/sub-1/image.png'];
        yield 'Path & file path prefix slash +2 subs' => ['55x66/crop/sub-1/sub-2/image.png', '/{width}x{height}/{action}/{file}', 55, 66, Action::createCrop(), '/sub-1/sub-2/image.png'];
    }

    /** @dataProvider providerNames */
    public function testGenerate(string $expected, string $pattern, int $width, int $height, Action $action, string $filePath): void
    {
        $generator = new NameGenerator($pattern);

        static::assertSame($expected, $generator->generate($width, $height, (string) $action, $filePath));
    }

    /** @dataProvider providerNames */
    public function testGenerateFromJob(string $expected, string $pattern, int $width, int $height, Action $action, string $filePath): void
    {
        $generator = new NameGenerator($pattern);
        $job = $this->createConfiguredMock(JobInterface::class, [
            'getRequestPath' => $filePath,
            'getAction' => $action,
            'getTargetDimensions' => new Dimensions($width, $height),
            'getRequestFilePath' => $filePath,
        ]);

        static::assertSame($expected, $generator->generateFromJob($job));
    }
}
