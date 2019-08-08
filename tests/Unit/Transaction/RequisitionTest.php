<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Transaction;

use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Dimensions;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use Camelot\ImageAsset\Transaction\Requisition;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Transaction\Requisition
 * @covers \Camelot\ImageAsset\Transaction\PhaseTrait
 */
final class RequisitionTest extends TestCase
{
    use PhaseTestTrait;

    public function testSetRequestPath(): void
    {
        static::assertSame('foo', Requisition::create()->setRequestPath('foo')->getRequestPath());
    }

    public function testSetRequestImage(): void
    {
        /** @var ImageInterface $expected */
        $expected = FilesystemMockBuilder::create()->createImages()->get('default.png');
        static::assertSame($expected, Requisition::create()->setRequestImage($expected)->getRequestImage());
    }

    public function testSetAction(): void
    {
        $expected = Action::createCrop();
        static::assertSame($expected, Requisition::create()->setAction($expected)->getAction());
    }

    public function testSetTargetDimensions(): void
    {
        $expected = new Dimensions();
        static::assertSame($expected, Requisition::create()->setTargetDimensions($expected)->getTargetDimensions());
    }
}
