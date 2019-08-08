<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Filesystem;

use Camelot\ImageAsset\Bridge\Symfony\Mime\MimeTypeGuesserFactory;
use Camelot\ImageAsset\Exception\FileNotFoundException;
use Camelot\ImageAsset\Exception\UnsupportedFileTypeException;
use const DIRECTORY_SEPARATOR;

final class FileFactory
{
    public static function create(FilesystemInterface $filesystem, string $filename): FileInterface
    {
        if (!$filesystem->exists($filename)) {
            throw new FileNotFoundException(sprintf('File not found "%s"' . PHP_EOL . 'Filesystem base: "%s".', $filename, $filesystem->getMountPath()), $filename);
        }

        try {
            return self::createTyped($filesystem, $filename);
        } catch (UnsupportedFileTypeException $e) {
            return new File($filesystem, $filename);
        }
    }

    private static function createTyped(FilesystemInterface $filesystem, string $filename): FileInterface
    {
        $mimeType = MimeTypeGuesserFactory::create()->guessMimeType($filesystem->getMountPath() . DIRECTORY_SEPARATOR . $filename);
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/png':
            case 'image/gif':
            case 'image/bmp':
            case 'image/svg':
            case 'image/svg+xml':
            case 'image/webp':
                return new Image($filesystem, $filename);

                break;
            default:
                throw new UnsupportedFileTypeException($mimeType, $filename);
        }
    }
}
