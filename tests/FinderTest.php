<?php

declare(strict_types=1);

namespace Camelot\ImageAssets\Tests;

use Camelot\Filesystem;
use Camelot\Filesystem\Adapter\Local;
use Camelot\ImageAssets\Finder;
use PHPUnit\Framework\TestCase;

class FinderTest extends TestCase
{
    /** @var Filesystem\Manager */
    protected $fs;
    /** @var Finder */
    protected $finder;

    public function setup(): void
    {
        $samples = new Filesystem\Filesystem(new Local(__DIR__ . '/images/samples'));
        $subdir = new Filesystem\Filesystem(new Local(__DIR__ . '/images/subdir'));
        $images = new Filesystem\Filesystem(new Local(__DIR__ . '/images'));
        $filesystems = [
            'samples' => $samples,
            'subdir' => $subdir,
            'images' => $images,
        ];

        $this->fs = new Filesystem\Manager($filesystems);

        $default = $images->getImage('samples/sample1.jpg');
        $this->finder = new Finder($this->fs, array_keys($filesystems), $default);
    }

    public function testFind(): void
    {
        $image = $this->finder->find('generic-logo.png');

        $this->assertSame($this->fs->getFilesystem('images'), $image->getFilesystem());
        $this->assertSame('generic-logo.png', $image->getPath());
    }

    public function testImageNotFoundUsesDefault(): void
    {
        $image = $this->finder->find('herp/derp.png');

        $this->assertSame($this->fs->getFilesystem('images'), $image->getFilesystem());
        $this->assertSame('samples/sample1.jpg', $image->getPath());
    }
}
