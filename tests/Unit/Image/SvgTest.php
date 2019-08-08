<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Image;

use Camelot\ImageAsset\Exception\IOException;
use Camelot\ImageAsset\Image\Svg;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Image\Svg
 */
final class SvgTest extends TestCase
{
    public function testCreateFromString(): void
    {
        Svg::createFromString(FilesystemMockBuilder::create()->createImages()->read('svg.svg'));

        $this->addToAssertionCount(1);
    }

    public function testCreateFromStringInvalid(): void
    {
        $this->expectException(IOException::class);

        Svg::createFromString('<notsvg></notsvg>');
    }
}
