<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Bridge\Symfony\DependencyInjection;

use Camelot\ImageAsset\Bridge\Symfony\DependencyInjection\CamelotImageAssetExtension;
use Camelot\ImageAsset\Controller\ImageAliasController;
use Camelot\ImageAsset\Controller\ImageController;
use Camelot\ImageAsset\Image\Attributes\Aliases;
use Camelot\ImageAsset\Responder\ImageResponderInterface;
use Camelot\ImageAsset\Routing\ImageAssetLoader;
use Camelot\ImageAsset\Thumbnail\Creator;
use Camelot\ImageAsset\Transaction\Processor;
use Camelot\ImageAsset\Transaction\TransactionBuilder;
use Contao\ImagineSvg\Imagine;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Camelot\ImageAsset\Bridge\Symfony\DependencyInjection\CamelotImageAssetExtension
 */
final class CamelotImageAssetExtensionTest extends TestCase
{
    private $config = [
        'routing' => [
            'mount_point' => '/thumbs',
        ],
        'default_image' => [
            'path' => 'image-default.png',
            'filesystem' => 'camelot.image_asset.filesystem.bundle',
        ],
        'default_image_size' => [
            'width' => 1024,
            'height' => 768,
        ],
        'error_image' => [
            'path' => 'image-error.png',
            'filesystem' => 'camelot.image_asset.filesystem.bundle',
        ],
        'cache_time' => null,
        'limit_upscaling' => true,
        'only_aliases' => false,
        'aliases' => [
            '123x246' => [
                'image_size' => [
                    'width' => 1024,
                    'height' => 768,
                ],
                'action' => 'crop',
            ],
        ],
    ];

    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $extension = new CamelotImageAssetExtension();
        $extension->load([$this->config], $container);

        static::assertTrue($container->has('camelot.image_asset.default_image_size'));
        static::assertTrue($container->has('camelot.image_asset.default_image_background'));
        static::assertTrue($container->has('camelot.image_asset.filesystem.bundle'));
        static::assertTrue($container->has('camelot.image_asset.filesystem.public'));
        static::assertTrue($container->has('camelot.image_asset.filesystem.thumbs'));
        static::assertTrue($container->has('camelot.thumbnails._alias_123x246'));

        static::assertTrue($container->has(ImageController::class));
        static::assertTrue($container->has(ImageAliasController::class));

        static::assertTrue($container->has(Aliases::class));
        static::assertTrue($container->has(Processor::class));
        static::assertTrue($container->has(Creator::class));
        static::assertTrue($container->has(ImageResponderInterface::class));
        static::assertTrue($container->has(TransactionBuilder::class));
        static::assertTrue($container->has(ImageAssetLoader::class));
        static::assertTrue($container->has(Imagine::class));
    }
}
