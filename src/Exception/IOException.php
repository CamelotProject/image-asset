<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Exception;

use Throwable;

/**
 * Exception thrown when a filesystem operation failure happens.
 */
class IOException extends RuntimeException
{
    /** @var ?string */
    private $path;

    public function __construct(string $message, ?string $path = null, int $code = 0, ?Throwable $previous = null)
    {
        $this->path = $path;
        parent::__construct($message, $code, $previous);
    }

    /** Returns the associated path for the exception. */
    public function getPath(): ?string
    {
        return $this->path;
    }
}
