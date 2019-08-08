<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Responder;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ImageResponderInterface
{
    /** Serve a request for an image. */
    public function getThumbnail(Request $request, string $file, string $action, int $width, int $height): Response;

    /** Returns a thumbnail response. */
    public function getThumbnailFromAlias(Request $request, string $file, string $alias): Response;
}
