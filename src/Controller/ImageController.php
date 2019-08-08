<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Controller;

use Camelot\ImageAsset\Responder\ImageResponderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ImageController extends AbstractController
{
    public function __invoke(ImageResponderInterface $responder, Request $request, int $width, int $height, string $action, string $file): Response
    {
        return $responder->getThumbnail($request, $file, $action, $width, $height);
    }
}
