<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Bridge\Symfony\Mime;

use Camelot\Thrower\Thrower;
use ErrorException;
use finfo;
use Symfony\Component\Mime\Exception\InvalidArgumentException;
use Symfony\Component\Mime\FileBinaryMimeTypeGuesser;
use Throwable;

/**
 * @internal
 */
final class BinaryMimeTypeGuesser extends FileBinaryMimeTypeGuesser
{
    public function __construct()
    {
        parent::__construct();
    }

    public function guessMimeType(string $path): ?string
    {
        try {
            $isFile = Thrower::call('is_file', $path);
        } catch (Throwable $e) {
            $isFile = false;
        }

        if ($isFile) {
            try {
                return parent::guessMimeType($path);
            } catch (InvalidArgumentException $e) { // @codeCoverageIgnore
                // Try below
            }
        }

        try {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $type = $finfo->buffer($path, FILEINFO_MIME_TYPE) ?: null;
        } catch (ErrorException $e) {  // @codeCoverageIgnore
            return null;               // @codeCoverageIgnore
        }

        $type = $type === 'image/svg' ? 'image/svg+xml' : $type;

        if (!preg_match('#^([a-z0-9\-]+/[a-z0-9\-.+]+)#i', $type, $match)) {
            // it's not a type, but an error message
            return null; // @codeCoverageIgnore
        }

        return $match[1] === 'image/svg' ? 'image/svg+xml' : $match[1];
    }
}
