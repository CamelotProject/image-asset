<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Responder;

use Camelot\ImageAsset\Exception\ExceptionInterface;
use Camelot\ImageAsset\Exception\NotFoundHttpException;
use Camelot\ImageAsset\Exception\RuntimeException;
use Camelot\ImageAsset\Filesystem\FilesystemInterface;
use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Attributes\Aliases;
use Camelot\ImageAsset\Image\Dimensions;
use Camelot\ImageAsset\Image\FallbackInterface;
use Camelot\ImageAsset\Response\Response as ThumbnailResponse;
use Camelot\ImageAsset\Thumbnail\ThumbnailInterface;
use Camelot\ImageAsset\Transaction\ProcessorInterface;
use Camelot\ImageAsset\Transaction\RequisitionInterface;
use Camelot\ImageAsset\Transaction\TransactionBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ImageResponder implements ImageResponderInterface
{
    /** @var TransactionBuilder */
    private $transactionBuilder;
    /** @var ProcessorInterface */
    private $processor;
    /** @var FallbackInterface */
    private $fallback;
    /** @var FilesystemInterface */
    private $filesystem;
    /** @var LoggerInterface */
    private $logger;
    /** @var Aliases */
    private $aliases;
    /** @var ?int */
    private $cacheTime;
    /** @var bool */
    private $debug;

    public function __construct(TransactionBuilder $transactionBuilder, ProcessorInterface $processor, FallbackInterface $fallback, FilesystemInterface $filesystem, LoggerInterface $logger, Aliases $aliases, ?int $cacheTime, bool $debug)
    {
        $this->transactionBuilder = $transactionBuilder;
        $this->processor = $processor;
        $this->fallback = $fallback;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
        $this->aliases = $aliases;
        $this->cacheTime = $cacheTime;
        $this->debug = $debug;
    }

    public function getThumbnail(Request $request, string $file, string $action, int $width, int $height): Response
    {
        $action = Action::resolve($action);
        if (strpos($file, '@2x') !== false) {
            $file = str_replace('@2x', '', $file);
            $width *= 2;
            $height *= 2;
        }

        try {
            return $this->handleTransaction($file, $action, $width, $height, Response::HTTP_OK);
        } catch (Throwable $e) {
            return $this->handleException($e, $action, $width, $height);
        }
    }

    /**
     * Returns a thumbnail response.
     */
    public function getThumbnailFromAlias(Request $request, string $file, string $alias): Response
    {
        if (!$this->aliases->hasAlias($alias)) {
            // Return 403 response if restricted to aliases, or throw an exception if debug & logged in
            return $this->getErrorImageResponse(Action::CROP, 0, 0, Response::HTTP_FORBIDDEN);
        }
        $config = $this->aliases->getAlias($alias);

        try {
            return $this->handleTransaction($file, $config->getAction(), $config->getWidth(), $config->getHeight(), Response::HTTP_OK);
        } catch (Throwable $e) {
            return $this->handleException($e, $config->getAction(), $config->getWidth(), $config->getHeight());
        }
    }

    private function getDefaultImageResponse(string $action, int $width, int $height, int $returnCode): Response
    {
        return $this->handleTransaction($this->fallback->getDefaultImage()->getRelativePathname(), $action, $width, $height, $returnCode);
    }

    private function getErrorImageResponse(string $action, int $width, int $height, int $statusCode): Response
    {
        return $this->handleTransaction($this->fallback->getErrorImage()->getRelativePathname(), $action, $width, $height, $statusCode);
    }

    private function handleTransaction(string $file, string $action, int $width, int $height, int $statusCode): Response
    {
        if (!$this->filesystem->exists($file)) {
            throw new NotFoundHttpException($file);
        }
        /** @var ImageInterface $image */
        $image = $this->filesystem->get($file);
        $transaction = $this->transactionBuilder->createTransaction();
        /** @var RequisitionInterface $requisition */
        $requisition = $transaction->getCurrent();
        $requisition
            ->setRequestPath($file)
            ->setAction(Action::create($action))
            ->setRequestImage($image)
            ->setTargetDimensions(new Dimensions($width, $height))
        ;
        $thumbnail = $this->processor->process($transaction);

        return $this->getResponse($thumbnail, $statusCode);
    }

    /**
     * @throws Throwable
     */
    private function handleException(Throwable $e, string $action, int $width, int $height): Response
    {
        $e = $e instanceof ExceptionInterface ? $e : new RuntimeException('Third party exception raised.', 255, $e);
        if ($this->debug) {
            throw $e; // @codeCoverageIgnore
        }
        if ($e instanceof NotFoundHttpException) {
            return $this->getDefaultImageResponse($action, $width, $height, Response::HTTP_NOT_FOUND);
        }

        return $this->getErrorImageResponse($action, $width, $height, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    private function getResponse(ThumbnailInterface $thumbnail, int $statusCode): Response
    {
        $response = new ThumbnailResponse($thumbnail);
        $contentType = $thumbnail->getImage()->getMimeType();
        $response
            ->setStatusCode($statusCode)
            ->setMaxAge($this->cacheTime)
            ->setSharedMaxAge($this->cacheTime)
            ->headers->set('Content-Type', $contentType)
        ;

        return $response;
    }
}
