<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Command\Helper;

use Camelot\ImageAsset\Command\Helper\PathParameters;
use Camelot\ImageAsset\Exception\RuntimeException;
use Camelot\ImageAsset\Filesystem\FileInterface;
use Camelot\ImageAsset\Filesystem\FilesystemInterface;
use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use PHPUnit\Framework\TestCase;
use function dirname;

/**
 * @covers \Camelot\ImageAsset\Command\Helper\PathParameters
 */
final class PathParametersTest extends TestCase
{
    public function providerParameters(): iterable
    {
        $builder = FilesystemMockBuilder::create();
        $projectRoot = dirname($builder->createPublic()->getMountPath());
        $outputPathname = $builder->createThumbs()->getMountPath() . '/default.png';

        $inputRelativePathname = 'public/images/default.png';
        $outputPathRelativePath = str_replace($projectRoot . '/', '', $outputPathname);

        yield 'Relative input, relative output' => [$inputRelativePathname, $outputPathRelativePath];
        yield 'Relative input, absolute output' => [$inputRelativePathname, $outputPathname];
        yield 'Absolute input, relative output' => ["$projectRoot/$inputRelativePathname", $outputPathRelativePath];
        yield 'Absolute input, absolute output' => ["$projectRoot/$inputRelativePathname", $outputPathname];
    }

    /** @dataProvider providerParameters */
    public function testCreate(string $inputPath, string $outputPath): void
    {
        $parameters = PathParameters::create(realpath(__DIR__ . '/../../../Fixtures/App'), $inputPath, $outputPath);

        static::assertInstanceOf(ImageInterface::class, $parameters->getInputFile());
        static::assertInstanceOf(FilesystemInterface::class, $parameters->getOutputFilesystem());
    }

    public function testCreateMissingInputFile(): void
    {
        $this->expectException(RuntimeException::class);
        PathParameters::create(realpath(__DIR__ . '/../../../Fixtures/App'), 'not-here.png', 'var/cache/test/');
    }

    public function testGetInputFilePath(): void
    {
        static::assertSame('input/path.ext', $this->getPathParameters()->getInputFilePath());
    }

    public function testGetInputFile(): void
    {
        $inputFile = $this->createMock(FileInterface::class);
        static::assertSame($inputFile, $this->getPathParameters(null, $inputFile)->getInputFile());
    }

    public function testGetOutputPath(): void
    {
        static::assertSame('output/path', $this->getPathParameters()->getOutputPath());
    }

    public function testGetOutputFilesystem(): void
    {
        $outputFilesystem = FilesystemMockBuilder::create()->createThumbs();
        static::assertSame($outputFilesystem, $this->getPathParameters(null, null, null, $outputFilesystem)->getOutputFilesystem());
    }

    private function getPathParameters(?string $inputFilePath = null, ?FileInterface $inputFile = null, ?string $outputPath = null, ?FilesystemInterface $outputFilesystem = null): PathParameters
    {
        return new PathParameters(
            $inputFilePath ?: 'input/path.ext',
            $inputFile ?: $this->createMock(FileInterface::class),
            $outputPath ?: 'output/path',
            $outputFilesystem ?: FilesystemMockBuilder::create()->createThumbs(),
        );
    }
}
