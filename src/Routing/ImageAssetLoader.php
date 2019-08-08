<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Routing;

use Camelot\ImageAsset\Exception\RuntimeException;
use Camelot\ImageAsset\Image\Attributes\Aliases;
use Camelot\ImageAsset\ImageAsset;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class ImageAssetLoader extends Loader
{
    /** @var string */
    private $mountPoint;
    /** @var string */
    private $controllerImage;
    /** @var string */
    private $pathImage;
    /** @var string */
    private $controllerImageAlias;
    /** @var string */
    private $pathImageAlias;
    /** @var Aliases */
    private $aliases;
    /** @var bool */
    private $isLoaded = false;

    public function __construct(string $mountPoint, string $controllerImage, string $pathImage, string $controllerImageAlias, string $pathImageAlias, Aliases $aliases)
    {
        $this->mountPoint = $mountPoint;
        $this->controllerImage = $controllerImage;
        $this->pathImage = $pathImage;
        $this->controllerImageAlias = $controllerImageAlias;
        $this->pathImageAlias = $pathImageAlias;
        $this->aliases = $aliases;
    }

    public function load($resource, $type = null): RouteCollection
    {
        if ($this->isLoaded === true) {
            throw new RuntimeException(sprintf('Can not re-add the %s loader', __CLASS__));
        }
        $routes = new RouteCollection();

        $this->addThumbnailRoute($routes);
        $this->addThumbnailAliasRoute($routes);

        $this->isLoaded = true;

        return $routes;
    }

    public function supports($resource, $type = null): bool
    {
        return $type === 'image_asset';
    }

    private function addThumbnailRoute(RouteCollection $routes): void
    {
        $path = $this->mountPoint . '/' . ltrim($this->pathImage, '/');
        $defaults = [
            '_controller' => $this->controllerImage,
        ];
        $requirements = [
            'width' => '\d+',
            'height' => '\d+',
            'action' => '(b|c|f|r|border|crop|fit|resize)',
            'file' => '.+',
        ];
        $route = new Route($path, $defaults, $requirements);
        $routes->add(ImageAsset::THUMBNAIL_ROUTE, $route);
    }

    private function addThumbnailAliasRoute(RouteCollection $routes): void
    {
        $regex = $this->aliases->getRouterRegex();
        $path = $this->mountPoint . '/' . ltrim($this->pathImageAlias, '/');
        $defaults = [
            '_controller' => $this->controllerImageAlias,
        ];
        $requirements = [
            'alias' => "({$regex})",
            'file' => '.+',
        ];
        $route = new Route($path, $defaults, $requirements);
        $routes->add(ImageAsset::THUMBNAIL_ALIAS_ROUTE, $route);
    }
}
