<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Filesystem;

use Camelot\ImageAsset\Exception\BadMethodCallException;
use Camelot\ImageAsset\Exception\ResolverIOException;
use Camelot\ImageAsset\Filesystem\Filesystem;
use Camelot\ImageAsset\Filesystem\ResolverFilesystem;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Filesystem\ResolverFilesystem
 */
final class ResolverFilesystemTest extends TestCase
{
    use FilesystemTestTrait;

    public function testGetBasePath(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->getMockFilesystem()->getMountPath();
    }

    public function testGetBasePaths(): void
    {
        $filesystem = FilesystemMockBuilder::create()->createScratch();
        $expected = $filesystem->getMountPath();

        static::assertSame([$expected], $this->getMockFilesystem($expected)->getBasePaths());
    }

    public function testGetFileInvalid(): void
    {
        $this->expectException(ResolverIOException::class);

        $this->getImageFilesystem()->get('nothing-here.jpg');
    }

    protected function getImageFilesystem(): ResolverFilesystem
    {
        return new ResolverFilesystem([FilesystemMockBuilder::create()->createImages()]);
    }

    protected function getMockFilesystem(?string $mountPath = null, bool $exists = true): ResolverFilesystem
    {
        $this->decorated
            ->expects(static::any())
            ->method('exists')
            ->willReturn($exists)
        ;
        $filesystem = new Filesystem($mountPath ?: $this->tmpDir, $this->decorated);

        return new ResolverFilesystem([$filesystem]);
    }
}
