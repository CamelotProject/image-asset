<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Controller;

use Camelot\ImageAsset\Responder\ImageResponderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ImageAliasController extends AbstractController
{
    public function __invoke(ImageResponderInterface $responder, Request $request, string $alias, string $file): Response
    {
        return $responder->getThumbnailFromAlias($request, $file, $alias);
    }
}
