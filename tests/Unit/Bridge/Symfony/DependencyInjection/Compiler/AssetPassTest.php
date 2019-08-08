<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Bridge\Symfony\DependencyInjection\Compiler;

use Camelot\ImageAsset\Bridge\Symfony\DependencyInjection\Compiler\AssetPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers \Camelot\ImageAsset\Bridge\Symfony\DependencyInjection\Compiler\AssetPass
 */
final class AssetPassTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $definition = new Definition();
        $definition->setArgument(0, '');
        $definition->setArgument(1, []);
        $container->setDefinition('assets.packages', $definition);
        $container->prependExtensionConfig('camelot_image_asset', []);

        $pass = new AssetPass();
        $pass->process($container);

        static::assertTrue($container->hasDefinition('assets._package_thumbnails'));
    }

    public function testProcessNoAssets(): void
    {
        $container = new ContainerBuilder();
        $pass = new AssetPass();
        $pass->process($container);

        static::assertFalse($container->hasDefinition('assets._package_thumbnails'));
    }
}
