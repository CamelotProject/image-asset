<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Thumbnail;

use Camelot\ImageAsset\Exception\InvalidArgumentException;
use Camelot\ImageAsset\Thumbnail\Manifest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * @covers \Camelot\ImageAsset\Thumbnail\Manifest
 */
final class ManifestTest extends TestCase
{
    public function testGetUncached(): void
    {
        $manifest = new Manifest(new ArrayAdapter());

        static::assertNull($manifest->get('foo.bar'));
    }

    public function testGetCached(): void
    {
        $manifest = new Manifest(new ArrayAdapter());
        $manifest->set('foo.bar', 'haha');

        static::assertSame('haha', $manifest->get('foo.bar'));
    }

    public function testGetException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $manifest = new Manifest(new ArrayAdapter());
        $manifest->get('foo');
    }

    public function testSetException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $manifest = new Manifest(new ArrayAdapter());
        $manifest->set('foo', 'bar');
    }
}
