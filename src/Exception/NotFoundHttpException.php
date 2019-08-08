<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException as SymfonyNotFoundHttpException;
use Throwable;

/**
 * @codeCoverageIgnore
 */
class NotFoundHttpException extends SymfonyNotFoundHttpException implements ExceptionInterface
{
    public function __construct(string $requestPath, Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct(sprintf('There was an error with the thumbnail image requested: %s', $requestPath), $previous, $code, $headers);
    }
}
