<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\EventSubscriber;

use Camelot\ImageAsset\Event\CreatorEvent;
use Camelot\ImageAsset\Thumbnail\Watermarker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CreatorEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): iterable
    {
        yield CreatorEvent::THUMBNAIL_CREATE => 'onThumbnailCreate';
    }

    public function onThumbnailCreate(CreatorEvent $event): CreatorEvent
    {
        $this->addWatermark($event);

        return $event;
    }

    private function addWatermark(CreatorEvent $event): void
    {
        $watermarker = new Watermarker();
        $data = $event->getData();
    }
}
