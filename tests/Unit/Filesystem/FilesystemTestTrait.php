<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Filesystem;

use Camelot\ImageAsset\Exception\IOException;
use Camelot\ImageAsset\Filesystem\FileInterface;
use Camelot\ImageAsset\Filesystem\FilesystemInterface;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount;
use PHPUnit\Framework\MockObject\Matcher\InvokedCount;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Filesystem\Exception\IOException as SymfonyIOException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

/**
 * @method static void assertSame($expected, $actual, string $message = '')
 * @method static void assertTrue($condition, string $message = '')
 * @method static void assertFalse($condition, string $message = '')
 * @method static void assertIsString($condition, string $message = '')
 * @method static void assertInstanceOf(string $expected, $actual, string $message = '')
 * @method void            expectException(string $exception)
 * @method void            expectExceptionMessage(string $message)
 * @method void            expectExceptionMessageRegExp(string $messageRegExp)
 * @method MockObject      createMock($originalClassName)
 * @method AnyInvokedCount any()
 * @method InvokedCount    once()
 */
trait FilesystemTestTrait
{
    /** @var string */
    protected $tmpDir;
    /** @var MockObject|SymfonyFilesystem */
    /** @var SymfonyFilesystem */
    protected $decorated;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpDir = FilesystemMockBuilder::create()->createScratch()->getMountPath();
        $this->decorated = $this->createMock(SymfonyFilesystem::class);
    }

    public function testCreate(): void
    {
        self::assertInstanceOf(FileInterface::class, $this->getImageFilesystem()->create('default.png'));
    }

    public function testGet(): void
    {
        self::assertInstanceOf(FileInterface::class, $this->getImageFilesystem()->get('default.png'));
    }

    public function testExists(): void
    {
        self::assertTrue($this->getImageFilesystem()->exists('default.png'));
    }

    public function testDoesNotExists(): void
    {
        self::assertFalse($this->getImageFilesystem()->exists('nothing-here.png'));
    }

    public function testRead(): void
    {
        self::assertIsString($this->getImageFilesystem()->read('default.png'));
    }

    public function testReadException(): void
    {
        $this->expectException(IOException::class);
        $this->expectExceptionMessageRegExp('#Failed to read file .default.gnp. on filesystem base.+tests.Fixtures.App.public.images.#');

        $this->getImageFilesystem()->read('default.gnp');
    }

    public function testRename(): void
    {
        $filesystem = $this->getMockFilesystem();
        $this->decorated
            ->expects($this->once())
            ->method('rename')
            ->with($this->tmpDir . '/foo', $this->tmpDir . '/bar', true)
        ;
        $filesystem->rename('foo', 'bar', true);
    }

    public function testRenameException(): void
    {
        $this->expectException(IOException::class);
        $this->expectExceptionMessageRegExp('#Failed to rename file .foo.+#m');

        $filesystem = $this->getMockFilesystem();
        $this->decorated
            ->expects($this->once())
            ->method('rename')
            ->willThrowException(new SymfonyIOException('An error from the decorated class'))
        ;
        $filesystem->rename('foo', 'bar');
    }

    public function testWrite(): void
    {
        $filesystem = $this->getMockFilesystem();
        $this->decorated
            ->expects($this->once())
            ->method('dumpFile')
            ->with($this->tmpDir . '/foo', 'bar')
        ;
        $filesystem->write('foo', 'bar');
    }

    public function testWriteException(): void
    {
        $this->expectException(IOException::class);
        $this->expectExceptionMessageRegExp('#Failed to write to file .foo.+#m');

        $filesystem = $this->getMockFilesystem();
        $this->decorated
            ->expects($this->once())
            ->method('dumpFile')
            ->willThrowException(new SymfonyIOException('An error from the decorated class'))
        ;
        $filesystem->write('foo', 'bar');
    }

    abstract protected function getImageFilesystem(): FilesystemInterface;

    abstract protected function getMockFilesystem(?string $mountPath = null, ?string $publicDir = null, bool $exists = true): FilesystemInterface;
}
