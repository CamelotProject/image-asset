<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Fixtures\Routing;

use Camelot\ImageAsset\Controller\ImageAliasController;
use Camelot\ImageAsset\Controller\ImageController;
use Camelot\ImageAsset\Image\Attributes\Aliases;
use Camelot\ImageAsset\Routing\ImageAssetLoader;
use Camelot\ImageAsset\Tests\Fixtures\Image\Attributes\AliasesMockBuilder;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;

final class UrlGeneratorMockBuilder
{
    public static function create(?string $mountPoint = null, ?string $controllerImage = null, ?string $pathImage = null, ?string $controllerImageAlias = null, ?string $pathImageAlias = null, ?Aliases $aliases = null): UrlGenerator
    {
        $loader = new ImageAssetLoader(
            $mountPoint ?: (string) new MountPathMockBuilder(),
            $controllerImage ?: ImageController::class,
            $pathImage ?: '{width}x{height}/{action}/{file}',
            $controllerImageAlias ?: ImageAliasController::class,
            $pathImageAlias ?: '{alias}/{file}',
            $aliases ?: AliasesMockBuilder::create(),
        );

        return new UrlGenerator($loader->load(null), new RequestContext());
    }
}
