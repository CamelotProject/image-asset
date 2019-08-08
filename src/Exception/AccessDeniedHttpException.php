<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Exception;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException as SymfonyAccessDeniedHttpException;

class AccessDeniedHttpException extends SymfonyAccessDeniedHttpException implements ExceptionInterface
{
}
