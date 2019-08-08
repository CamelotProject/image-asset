<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Routing;

use Camelot\ImageAsset\Controller\ImageAliasController;
use Camelot\ImageAsset\Controller\ImageController;
use Camelot\ImageAsset\Exception\RuntimeException;
use Camelot\ImageAsset\Image\Attributes\Aliases;
use Camelot\ImageAsset\ImageAsset;
use Camelot\ImageAsset\Routing\ImageAssetLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * @covers \Camelot\ImageAsset\Routing\ImageAssetLoader
 */
final class ImageAssetLoaderTest extends TestCase
{
    private const MOUNT_POINT = '/woldberg';

    public function providerLoad(): iterable
    {
        yield [ImageAsset::THUMBNAIL_ROUTE];
        yield [ImageAsset::THUMBNAIL_ALIAS_ROUTE];
    }

    /** @dataProvider providerLoad */
    public function testLoad(string $routeName): void
    {
        $loader = $this->getImageAssetLoader();
        $routes = $loader->load(null);

        static::assertInstanceOf(RouteCollection::class, $routes);
        static::assertInstanceOf(Route::class, $routes->get($routeName));
    }

    public function providerLoadRoutes(): iterable
    {
        $tr = ImageAsset::THUMBNAIL_ROUTE;
        $tar = ImageAsset::THUMBNAIL_ALIAS_ROUTE;

        yield "$tr getPath" => [$tr, 'getPath', self::MOUNT_POINT . '/{width}x{height}/{action}/{file}'];
        yield "$tar getPath" => [$tar, 'getPath', self::MOUNT_POINT . '/{alias}/{file}'];

        yield "$tr getDefaults" => [$tr, 'getDefaults', ['_controller' => ImageController::class]];
        yield "$tar getDefaults" => [$tar, 'getDefaults', ['_controller' => ImageAliasController::class]];

        yield "$tr getRequirements" => [$tr, 'getRequirements', [
             'width' => '\d+',
            'height' => '\d+',
            'action' => '(b|c|f|r|border|crop|fit|resize)',
            'file' => '.+',
        ]];
        yield "$tar getRequirements" => [$tar, 'getRequirements', [
            'alias' => '()',
            'file' => '.+',
        ]];
    }

    /** @dataProvider providerLoadRoutes */
    public function testLoadRoutes(string $routeName, string $method, $expected): void
    {
        $loader = $this->getImageAssetLoader();
        $routes = $loader->load(null);
        $route = $routes->get($routeName);

        static::assertSame($expected, $route->$method());
    }

    public function testLoadTwice(): void
    {
        $this->expectException(RuntimeException::class);
        $loader = $this->getImageAssetLoader();
        $loader->load(null);
        $loader->load(null);
    }

    public function testSupports(): void
    {
        $loader = $this->getImageAssetLoader();

        static::assertTrue($loader->supports(null, 'image_asset'));
    }

    private function getImageAssetLoader(): ImageAssetLoader
    {
        return new ImageAssetLoader(self::MOUNT_POINT, ImageController::class, '/{width}x{height}/{action}/{file}', ImageAliasController::class, '/{alias}/{file}', new Aliases([]));
    }
}
