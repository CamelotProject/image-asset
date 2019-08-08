<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Bridge\Symfony\DependencyInjection\Compiler;

use Camelot\ImageAsset\Bridge\Symfony\DependencyInjection\Configuration;
use Symfony\Component\Asset\PackageInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class AssetPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('assets.packages')) {
            return;
        }

        $configs = $container->getExtensionConfig('camelot_image_asset');
        $config = (new Processor())->processConfiguration(new Configuration(), $configs);

        if (interface_exists(PackageInterface::class)) {
            $packages = $container->getDefinition('assets.packages');
            $namedPackages = $packages->getArgument(1);

            $container->setDefinition('assets._package_thumbnails', $this->createPackageDefinition($config['routing']['mount_point'], new Reference('assets.empty_version_strategy')));
            $container->registerAliasForArgument('assets._package_thumbnails', PackageInterface::class, 'thumbnails.package');
            $namedPackages['thumbnails'] = new Reference('assets._package_thumbnails');

            $packages->replaceArgument(1, $namedPackages);
        }
    }

    /** Returns a definition for an asset package. */
    private function createPackageDefinition(string $basePath, Reference $version): ChildDefinition
    {
        $package = new ChildDefinition('assets.path_package');
        $package
            ->setPublic(false)
            ->replaceArgument(0, $basePath)
            ->replaceArgument(1, $version)
        ;

        return $package;
    }
}
