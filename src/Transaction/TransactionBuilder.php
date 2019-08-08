<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Transaction;

use Camelot\ImageAsset\Filesystem\FileInterface;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Dimensions;
use Camelot\ImageAsset\Image\FallbackInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class TransactionBuilder
{
    private FallbackInterface $fallback;
    private LoggerInterface $logger;

    public function __construct(FallbackInterface $fallback, LoggerInterface $logger = null)
    {
        $this->fallback = $fallback;
        $this->logger = $logger ?: new NullLogger();
    }

    public function createTransaction(?string $requestPath = null, ?Action $action = null, ?Dimensions $targetDimensions = null, ?FileInterface $requestImage = null): Transaction
    {
        return new Transaction(
            $this->createRequisition($requestPath, $action, $targetDimensions, $requestImage),
            fn (Transaction $transaction): JobInterface => Job::create($transaction->getCurrent()),
            $this->logger,
        );
    }

    public function createFromJob(JobInterface $job): Transaction
    {
        $req = Requisition::create()
            ->setRequestPath($job->getRequestPath())
            ->setAction($job->getAction())
            ->setTargetDimensions($job->getTargetDimensions())
            ->setRequestImage($job->getRequestImage())
        ;

        return new Transaction($req, fn (JobInterface $job) => $job, $this->logger);
    }

    private function createRequisition(?string $requestPath, ?Action $action, ?Dimensions $targetDimensions = null, ?FileInterface $requestImage = null): RequisitionInterface
    {
        $targetDimensions = $targetDimensions ?: $this->fallback->getDefaultDimensions();
        $requestImage = $requestImage ?: $this->fallback->getDefaultImage();
        $requisition = Requisition::create();
        if ($requestPath) {
            $requisition->setRequestPath($requestPath);
        }
        if ($action) {
            $requisition->setAction($action);
        }
        if ($targetDimensions) {
            $requisition->setTargetDimensions($targetDimensions);
        }
        if ($requestImage) {
            $requisition->setRequestImage($requestImage);
        }

        return $requisition;
    }
}
