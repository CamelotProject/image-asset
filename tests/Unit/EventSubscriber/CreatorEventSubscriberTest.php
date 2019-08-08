<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\EventSubscriber;

use Camelot\ImageAsset\Event\CreatorEvent;
use Camelot\ImageAsset\EventSubscriber\CreatorEventSubscriber;
use Camelot\ImageAsset\Transaction\JobInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\EventSubscriber\CreatorEventSubscriber
 */
final class CreatorEventSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        static::assertSame([CreatorEvent::THUMBNAIL_CREATE => 'onThumbnailCreate'], iterator_to_array(CreatorEventSubscriber::getSubscribedEvents()));
    }

    public function testOnThumbnailCreate(): void
    {
        static::assertInstanceOf(CreatorEvent::class, $this->getCreatorEventSubscriber()->onThumbnailCreate(new CreatorEvent($this->createMock(JobInterface::class), 'data')));
    }

    private function getCreatorEventSubscriber(): CreatorEventSubscriber
    {
        return new CreatorEventSubscriber();
    }
}
