<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Bridge\Symfony\DependencyInjection;

use Camelot\ImageAsset\Command\GenerateThumbsCommand;
use Camelot\ImageAsset\Filesystem\Filesystem;
use Camelot\ImageAsset\Image\Attributes\Alias;
use Camelot\ImageAsset\Image\Fallback;
use Camelot\ImageAsset\Responder\ImageResponder;
use Camelot\ImageAsset\Routing\ImageAssetLoader;
use Camelot\ImageAsset\Thumbnail\NameGenerator;
use Camelot\ImageAsset\Thumbnail\Rescaler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use function dirname;

final class CamelotImageAssetExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.xml');

        $config = $this->processConfiguration(new Configuration(), $configs);
        $publicDir = $this->getPublicDirectory($container);

        // Command
        $command = $container->getDefinition(GenerateThumbsCommand::class);
        $command->setArgument('$publicDir', $publicDir);

        // Image
        $imageDefaultSize = $container->getDefinition('camelot.image_asset.default_image_size');
        $imageDefaultSize->setArgument('$width', $config['default_image_size']['width']);
        $imageDefaultSize->setArgument('$height', $config['default_image_size']['height']);

        $fallback = $container->getDefinition(Fallback::class);
        $fallback->setArgument('$defaultImagePath', $config['default_image']['path']);
        $fallback->setArgument('$errorImagePath', $config['error_image']['path']);

        // Thumbnails
        $creator = $container->getDefinition(Rescaler::class);
        $creator->setArgument('$limitUpscaling', $config['limit_upscaling']);
        $nameGenerator = $container->getDefinition(NameGenerator::class);
        $nameGenerator->setArgument('$pattern', $config['routing']['image']['path']);

        // Routing
        $loader = $container->getDefinition(ImageAssetLoader::class);
        $loader->setArgument('$mountPoint', $config['routing']['mount_point']);
        $loader->setArgument('$controllerImage', $config['routing']['image']['controller']);
        $loader->setArgument('$pathImage', $config['routing']['image']['path']);
        $loader->setArgument('$controllerImageAlias', $config['routing']['image_alias']['controller']);
        $loader->setArgument('$pathImageAlias', $config['routing']['image_alias']['path']);

        $loader = $container->getDefinition(ImageResponder::class);
        $loader->setArgument('$cacheTime', $config['cache_time']);

        // Filesystems
        $publicFs = $container->getDefinition('camelot.image_asset.filesystem.public');
        $publicFs->setArgument('$mountPath', $publicDir);

        foreach ($config['image_dirs'] as $index => $imageDir) {
            $fs = new Definition(Filesystem::class);
            $fs->setArgument('$mountPath', $imageDir);
            $fs->addTag('camelot.image_asset.filesystem.image', ['priority' => 5]);
            $container->setDefinition('camelot.image_asset.filesystem.image_' . $index, $fs);
        }

        $thumbsFs = $container->getDefinition('camelot.image_asset.filesystem.thumbs');
        $thumbsFs->setArgument('$mountPath', $config['static_path']);

        $bundleFs = $container->getDefinition('camelot.image_asset.filesystem.bundle');
        $bundleFs->setArgument('$mountPath', $publicDir . '/bundles/' . str_replace('_', '', self::getAlias()));

        // Aliases
        foreach ($config['aliases'] as $name => $alias) {
            $definition = new Definition(Alias::class);
            $definition->setArgument('$name', $name);
            $definition->setArgument('$action', $alias['action']);
            $definition->setArgument('$width', $alias['image_size']['width']);
            $definition->setArgument('$height', $alias['image_size']['height']);

            $definition->addTag('camelot.thumbnails.alias');

            $container->setDefinition("camelot.thumbnails._alias_{$name}", $definition);
        }
    }

    /** @codeCoverageIgnore */
    private function getPublicDirectory(ContainerBuilder $container)
    {
        $defaultPublicDir = '%kernel.project_dir%/public';
        if (!$container->hasParameter('kernel.project_dir')) {
            return $defaultPublicDir;
        }
        $composerFilePath = $container->getParameter('kernel.project_dir') . '/composer.json';
        if (!file_exists($composerFilePath)) {
            return $defaultPublicDir;
        }
        $composerConfig = json_decode(file_get_contents($composerFilePath), true);

        if (isset($composerConfig['extra']['public-dir'])) {
            return '%kernel.project_dir%' . $composerConfig['extra']['public-dir'];
        }

        return $defaultPublicDir;
    }
}
