<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Thumbnail;

use Camelot\ImageAsset\Event\CreatorEvent;
use Camelot\ImageAsset\Exception\ExceptionInterface;
use Camelot\ImageAsset\Exception\InvalidImageException;
use Camelot\ImageAsset\Exception\NoFallbackException;
use Camelot\ImageAsset\Exception\NotFoundHttpException;
use Camelot\ImageAsset\Image\Type\SvgType;
use Camelot\ImageAsset\Transaction\JobInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Creates thumbnails.
 */
final class Creator implements CreatorInterface
{
    /** @var Resizer */
    private $resizer;
    /** @var Rescaler */
    private $rescaler;
    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(Rescaler $rescaler, Resizer $resizer, EventDispatcherInterface $dispatcher)
    {
        $this->rescaler = $rescaler;
        $this->resizer = $resizer;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ExceptionInterface
     */
    public function create(JobInterface $job): ?string
    {
        $this->assertValidImage($job);

        $this->rescaler->autoscale($job);
        $this->rescaler->checkForUpscale($job);

        if (strpos(SvgType::MIME, $job->getRequestImage()->getMimeType()) === 0) {
            return $this->resizer->resizeSvg($job);
        }
        $event = new CreatorEvent($job, $this->resizer->resize($job));
        $this->dispatcher->dispatch($event);

        return $event->getData();
    }

    /**
     * Verifies that the image's info can be read correctly.
     *
     * @throws NotFoundHttpException
     * @throws NoFallbackException
     */
    private function assertValidImage(JobInterface $job): void
    {
        $requestImage = $job->getRequestImage();
        if (!$requestImage->exists()) {
            throw new NotFoundHttpException($job->getRequestPath());
        }
        if ($requestImage->getInfo()->isValid()) {
            return;
        }

        throw new InvalidImageException(sprintf('Image file %s is invalid', $requestImage->getRelativePathname()));
    }
}
