<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Routing;

use Camelot\ImageAsset\Filesystem\FilesystemInterface;
use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Attributes\Aliases;
use Camelot\ImageAsset\Image\Dimensions;
use Camelot\ImageAsset\Image\Fallback;
use Camelot\ImageAsset\ImageAsset;
use Camelot\ImageAsset\Thumbnail\ManifestInterface;
use Camelot\ImageAsset\Transaction\ProcessorInterface;
use Camelot\ImageAsset\Transaction\TransactionBuilder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ThumbnailPathMatcher implements ThumbnailPathMatcherInterface
{
    /** @var ManifestInterface */
    private $manifest;
    /** @var Aliases */
    private $alases;
    /** @var Fallback */
    private $fallback;
    /** @var FilesystemInterface */
    private $filesystem;
    /** @var TransactionBuilder */
    private $transactionBuilder;
    /** @var ProcessorInterface */
    private $processor;
    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    public function __construct(ManifestInterface $manifest, Aliases $alases, Fallback $fallback, FilesystemInterface $filesystem, TransactionBuilder $transactionBuilder, ProcessorInterface $processor, UrlGeneratorInterface $urlGenerator)
    {
        $this->manifest = $manifest;
        $this->alases = $alases;
        $this->fallback = $fallback;
        $this->filesystem = $filesystem;
        $this->transactionBuilder = $transactionBuilder;
        $this->processor = $processor;
        $this->urlGenerator = $urlGenerator;
    }

    public function matchPath(string $filePath, string $action, int $width, int $height): string
    {
        $thumbPath = $this->manifest->get($filePath);
        if ($thumbPath) {
            return $thumbPath;
        }
        $action = Action::create($action);
        $dimensions = new Dimensions($width, $height);
        $this->alases->match($dimensions->getWidth(), $dimensions->getHeight(), (string) $action);

        return $this->doMatch($filePath, $action, $dimensions);
    }

    public function matchAlias(string $filePath, string $alias = null): string
    {
        if ($alias) {
            $alias = $this->alases->getAlias($alias);

            return $this->doMatch($filePath, Action::create($alias->getAction()), new Dimensions($alias->getWidth(), $alias->getHeight()));
        }

        return $this->doMatch($filePath, Action::createCrop(), $this->fallback->getDefaultDimensions());
    }

    private function doMatch(string $filePath, Action $action, Dimensions $dimensions): string
    {
        /** @var ImageInterface $image */
        $image = $this->filesystem->get($filePath);
        $transaction = $this->transactionBuilder->createTransaction($filePath, $action, $dimensions, $image);
        $this->processor->process($transaction);
        $url = $this->urlGenerator->generate(ImageAsset::THUMBNAIL_ROUTE, [
            'width' => $dimensions->getWidth(),
            'height' => $dimensions->getHeight(),
            'action' => (string) $action,
            'file' => $filePath,
        ]);
        $this->manifest->set($filePath, $url);

        return $url;
    }
}
