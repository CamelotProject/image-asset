<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Transaction;

use Camelot\ImageAsset\Exception\RuntimeException;
use Camelot\ImageAsset\Filesystem\FilesystemInterface;
use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Dimensions;
use Camelot\ImageAsset\Image\FallbackInterface;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FallbackMockBuilder;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use Camelot\ImageAsset\Transaction\JobInterface;
use Camelot\ImageAsset\Transaction\PhaseInterface;
use Camelot\ImageAsset\Transaction\PhaseTrait;
use Camelot\ImageAsset\Transaction\ProcessorInterface;
use PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount;
use PHPUnit\Framework\MockObject\Matcher\InvokedCount;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\RequestStack;

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
trait PhaseTestTrait
{
    /** @var ImageInterface */
    private $requestImage;
    /** @var FilesystemInterface */
    private $filesystem;
    /** @var ProcessorInterface */
    private $processor;
    /** @var RequestStack */
    private $requestStack;

    protected function setUp(): void
    {
        $this->filesystem = FilesystemMockBuilder::create()->createImages();
        $this->requestImage = $this->filesystem->get('default.png');
        $this->processor = $this->createMock(ProcessorInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
    }

    public function testCreate(): void
    {
        self::assertInstanceOf(PhaseInterface::class, $this->getPhase());
    }

    public function testCreateWithArgs(): void
    {
        $phase = $this->getPhase(['koala' => 42]);
        self::assertSame(42, $phase->getKoala());
    }

    public function testCreateInvalid(): void
    {
        $this->expectException(RuntimeException::class);
        $this->getPhase(['foo' => null]);
    }

    public function testGetTargetDimensions(): void
    {
        self::assertInstanceOf(Dimensions::class, $this->getPhase()->getTargetDimensions());
    }

    public function testGetRequestImage(): void
    {
        self::assertSame($this->requestImage, $this->getPhase()->getRequestImage());
    }

    public function testGetHash(): void
    {
        self::assertSame('default.png-crop-0-0', $this->getPhase()->getHash());
    }

    public function testGetRequestPath(): void
    {
        self::assertSame('/default.png', $this->getPhase()->getRequestPath());
    }

    public function testGetAction(): void
    {
        self::assertInstanceOf(Action::class, $this->getPhase()->getAction());
    }

    public function testGetFilePath(): void
    {
        self::assertSame('default.png', $this->getPhase()->getRequestFilePath());
    }

    public function providerInvalidPhase(): iterable
    {
        yield [null, $this->createMock(PhaseInterface::class)];
        yield [$this->createMock(JobInterface::class), $this->createMock(PhaseInterface::class)];
    }

    private function getPhase(iterable $args = []): PhaseInterface
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $fallback = FallbackMockBuilder::create($filesystem, new Dimensions(123, 456));

        $class = new class('/default.png', Action::createCrop(), new Dimensions(), $this->requestImage, $fallback) implements PhaseInterface {
            use PhaseTrait;

            /** @var ?ThumbnailInterface */
            public $thumbnail = null;
            /** @var ?FileInterface */
            public $file = null;
            /** @var int */
            public $koala = 0;

            public function __construct(
                string $requestPath,
                Action $action,
                Dimensions $targetDimensions,
                ImageInterface $requestImage,
                FallbackInterface $fallback
            ) {
                $this->requestPath = $requestPath;
                $this->action = $action;
                $this->targetDimensions = $targetDimensions;
                $this->requestImage = $requestImage;
                $this->fallback = $fallback;
            }

            public function getKoala(): int
            {
                return $this->koala;
            }
        };

        return $class::create($class, $args);
    }
}
