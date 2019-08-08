<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Exception;

use Throwable;

final class UnsupportedFileTypeException extends \InvalidArgumentException implements ExceptionInterface
{
    public function __construct(string $mimeType, string $filename, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('Unhandled image file: %s (%s)', $mimeType, $filename), $code, $previous);
    }
}
