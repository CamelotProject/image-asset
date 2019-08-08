<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Command\Helper;

use Camelot\ImageAsset\Exception\RuntimeException;
use Camelot\ImageAsset\Filesystem\FileInterface;
use Camelot\ImageAsset\Filesystem\Filesystem;
use Camelot\ImageAsset\Filesystem\FilesystemInterface;
use Webmozart\PathUtil\Path;
use function dirname;

final class PathParameters
{
    /** @var string */
    private $inputFilePath;
    /** @var FileInterface */
    private $inputFile;
    /** @var string */
    private $outputPath;
    /** @var FilesystemInterface */
    private $outputFilesystem;

    public function __construct(string $inputFilePath, FileInterface $inputFile, string $outputPath, FilesystemInterface $outputFilesystem)
    {
        $this->inputFilePath = $inputFilePath;
        $this->inputFile = $inputFile;
        $this->outputPath = $outputPath;
        $this->outputFilesystem = $outputFilesystem;
    }

    public static function create(string $projectDir, string $inputPath, string $outputPath): self
    {
        $sourcePath = Path::isAbsolute($inputPath) ? $inputPath : Path::makeAbsolute($inputPath, $projectDir);
        $sourceFilesystem = new Filesystem(dirname($sourcePath));
        $sourceFile = $sourceFilesystem->get(basename($sourcePath));

        $destinationPath = Path::isAbsolute($outputPath) ? $outputPath : Path::makeAbsolute($outputPath, $projectDir);
        $destinationFilesystem = new Filesystem(dirname($destinationPath));

        if (!$sourceFile->exists()) {
            throw new RuntimeException(sprintf('Input file %s does not exist', $sourceFile->getRelativePathname()));
        }

        return new self($sourcePath, $sourceFile, $destinationPath, $destinationFilesystem);
    }

    public function getInputFilePath(): string
    {
        return $this->inputFilePath;
    }

    public function getInputFile(): FileInterface
    {
        return $this->inputFile;
    }

    public function getOutputPath(): string
    {
        return $this->outputPath;
    }

    public function getOutputFilesystem(): FilesystemInterface
    {
        return $this->outputFilesystem;
    }
}
