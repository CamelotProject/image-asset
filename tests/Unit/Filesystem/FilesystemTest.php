<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Filesystem;

use Camelot\ImageAsset\Exception\FileNotFoundException;
use Camelot\ImageAsset\Filesystem\Filesystem;
use Camelot\ImageAsset\Filesystem\FinderInterface;
use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Camelot\ImageAsset\Filesystem\Filesystem
 */
final class FilesystemTest extends TestCase
{
    use FilesystemTestTrait;

    public function testFinder(): void
    {
        static::assertInstanceOf(FinderInterface::class, $this->getImageFilesystem()->finder());
    }

    public function testGetMountPath(): void
    {
        $filesystem = $this->getMockFilesystem();
        static::assertSame($this->tmpDir . '', $filesystem->getMountPath());
    }

    public function testGetFileInvalid(): void
    {
        $this->expectException(FileNotFoundException::class);

        $this->getImageFilesystem()->get('nothing-around.jpg');
    }

    public function providerResolve(): iterable
    {
        yield ['foo.bar', 'foo.bar', __DIR__];
        yield ['who/foo.bar', 'who/foo.bar', __DIR__];
        yield ['who/foo.bar', '/who/foo.bar', __DIR__];
    }

    /** @dataProvider providerResolve */
    public function testResolve(string $expected, string $filename, string $baseDir): void
    {
        $filesystem = $this->getMockFilesystem($baseDir);
        $rc = new ReflectionClass($filesystem);
        $rp = $rc->getMethod('resolve');
        $rp->setAccessible(true);
        /** @var ImageInterface $image */
        $image = $rp->invoke($filesystem, $filename);

        static::assertSame($expected, $image->getRelativePathname());
    }

    private function getImageFilesystem(): Filesystem
    {
        return FilesystemMockBuilder::create()->createImages();
    }

    private function getMockFilesystem(?string $mountPath = null, bool $exists = true): Filesystem
    {
        $this->decorated
            ->expects(static::any())
            ->method('exists')
            ->willReturn($exists)
        ;

        return new Filesystem($mountPath ?: $this->tmpDir, $this->decorated);
    }
}
