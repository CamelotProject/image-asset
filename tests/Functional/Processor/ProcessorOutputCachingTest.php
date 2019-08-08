<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Functional\Processor;

use Camelot\ImageAsset\Filesystem\Image;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Dimensions;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use Camelot\ImageAsset\Tests\Fixtures\Transaction\TransactionMockBuilder;
use Camelot\ImageAsset\Tests\Functional\KernelTestCase;
use Camelot\ImageAsset\Tests\ThumbnailAssertTrait;
use Camelot\ImageAsset\Transaction\ProcessorInterface;

/**
 * @group functional
 * @coversNothing
 */
final class ProcessorOutputCachingTest extends KernelTestCase
{
    use ThumbnailAssertTrait;

    public function providerActions(): iterable
    {
        yield Action::BORDER => ['placeholder.jpg', Action::createBorder(), new Dimensions(444, 333)];
        yield Action::CROP => ['placeholder.jpg', Action::createCrop(), new Dimensions(444, 333)];
        yield Action::FIT => ['placeholder.jpg', Action::createFit(), new Dimensions(444, 333)];
        yield Action::RESIZE => ['placeholder.jpg', Action::createResize(), new Dimensions(444, 333)];
    }

    /** @dataProvider providerActions */
    public function testThumbnail(string $filePath, Action $action, Dimensions $dimensions): void
    {
        static::bootKernel();
        $container = static::$container;

        /** @var ProcessorInterface $processor */
        $processor = $container->get(ProcessorInterface::class);
        $transaction = TransactionMockBuilder::createTransaction();
        $thumbnail = $processor->process($transaction);

        static::assertFileExists($thumbnail->getImage()->getPathname());
    }

    /** @dataProvider providerActions */
    public function testThumbnailPhysicalLocation(string $filePath, Action $action, Dimensions $dimensions): void
    {
        $builder = FilesystemMockBuilder::create();
        $filesystem = $builder->createThumbs();
        static::bootKernel();
        $container = static::$container;

        /** @var Image $requestImage */
        $requestImage = $builder->createImages()->get($filePath);
        $expectedRelative = sprintf('%sx%s/%s/%s', $dimensions->getWidth(), $dimensions->getHeight(), $action, $requestImage->getRelativePathname());

        /** @var ProcessorInterface $processor */
        $processor = $container->get(ProcessorInterface::class);
        $transaction = TransactionMockBuilder::createTransaction($filePath, $action, $dimensions->getWidth(), $dimensions->getHeight());
        $processor->process($transaction);

        $this->assertThumbnailLocation($filesystem->getMountPath(), $expectedRelative);
    }
}
