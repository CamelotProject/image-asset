<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Exception;

use Throwable;
use const PHP_EOL;

/**
 * Exception thrown when a Resolver filesystem operation failure happens.
 */
class ResolverIOException extends IOException
{
    /** @var IOException[] */
    private $exceptions;

    public function __construct(string $operation, array $exceptions, ?string $path = null, int $code = 0, ?Throwable $previous = null)
    {
        $this->exceptions = $exceptions;
        $messages = [];
        foreach ($this->exceptions as $exception) {
            $messages[] = $exception->getMessage();
        }
        $message = sprintf('Unable to perform %s operation on %s%sMessage traces%s%s', $operation, $path, PHP_EOL, PHP_EOL, implode(PHP_EOL, $messages));
        parent::__construct($message, null, $code, $previous);
    }
}
