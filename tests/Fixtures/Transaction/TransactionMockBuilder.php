<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Fixtures\Transaction;

use Camelot\ImageAsset\Filesystem\FilesystemInterface;
use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Attributes\Color;
use Camelot\ImageAsset\Image\Dimensions;
use Camelot\ImageAsset\Image\FallbackInterface;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FallbackMockBuilder;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use Camelot\ImageAsset\Tests\Fixtures\Thumbnail\ThumbnailMockBuilder;
use Camelot\ImageAsset\Thumbnail\Creator;
use Camelot\ImageAsset\Thumbnail\CreatorInterface;
use Camelot\ImageAsset\Thumbnail\NameGenerator;
use Camelot\ImageAsset\Thumbnail\Rescaler;
use Camelot\ImageAsset\Thumbnail\Resizer;
use Camelot\ImageAsset\Transaction\Job;
use Camelot\ImageAsset\Transaction\JobInterface;
use Camelot\ImageAsset\Transaction\Processor;
use Camelot\ImageAsset\Transaction\RequisitionInterface;
use Camelot\ImageAsset\Transaction\Transaction;
use Camelot\ImageAsset\Transaction\TransactionBuilder;
use Contao\ImagineSvg\Imagine;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class TransactionMockBuilder
{
    public static function createBuilder(?FallbackInterface $fallback = null): TransactionBuilder
    {
        return new TransactionBuilder($fallback ?: FallbackMockBuilder::create());
    }

    public static function createTransaction(string $requestPath = 'default.png', ?Action $action = null, int $width = 256, int $height = 128): Transaction
    {
        $fallback = FallbackMockBuilder::create();
        $builder = self::createBuilder($fallback);
        /** @var ImageInterface $image */
        $image = FilesystemMockBuilder::create()->createImages()->get($requestPath);
        $transaction = $builder->createTransaction($requestPath, $action ?: Action::createCrop(), new Dimensions($width, $height), $image);

        return $transaction;
    }

    public static function createTransactionWithCallable(?callable $job = null, ?RequisitionInterface $requisition = null, ?LoggerInterface $logger = null): Transaction
    {
        /** @var RequisitionInterface $requisition */
        $requisition = $requisition ?: self::createTransaction()->getCurrent();
        $job = $job ?: function (Transaction $t): JobInterface {
            return Job::create($t->getCurrent());
        };

        return new Transaction($requisition, $job, $logger ?: new NullLogger());
    }

    public static function createProcessor(?CreatorInterface $creator = null, ?FilesystemInterface $thumbsFs = null, ?NameGenerator $nameGenerator = null): Processor
    {
        return new Processor(
            $creator ?: new Creator(new Rescaler(false), new Resizer(new Imagine(), Color::white()), new EventDispatcher()),
            $nameGenerator = $nameGenerator ?: ThumbnailMockBuilder::createNameGenerator(),
            $thumbsFs ?: FilesystemMockBuilder::create()->createThumbs()
        );
    }
}
