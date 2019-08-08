<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Fixtures\App;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TestController extends AbstractController
{
    public function __invoke(Request $request): Response
    {
        return $this->render('index.html.twig');
    }
}
