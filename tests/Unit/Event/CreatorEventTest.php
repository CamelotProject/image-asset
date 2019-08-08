<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Event;

use Camelot\ImageAsset\Event\CreatorEvent;
use Camelot\ImageAsset\Transaction\JobInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Event\CreatorEvent
 */
final class CreatorEventTest extends TestCase
{
    public function testGetJob(): void
    {
        static::assertInstanceOf(JobInterface::class, $this->getCreatorEvent()->getJob());
    }

    public function testGetSetData(): void
    {
        static::assertSame('android', $this->getCreatorEvent()->setData('android')->getData());
    }

    private function getCreatorEvent(): CreatorEvent
    {
        return new CreatorEvent($this->createMock(JobInterface::class), 'robot');
    }
}
