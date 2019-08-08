<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Bridge\Symfony\DependencyInjection;

use Camelot\ImageAsset\Controller\ImageAliasController;
use Camelot\ImageAsset\Controller\ImageController;
use Camelot\ImageAsset\Image\Attributes\Action;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use function is_array;

/**
 * @codeCoverageIgnore
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('camelot_image_asset');
        $sizer = function ($v): array {
            if (is_array($v)) {
                return $v;
            }
            if (!preg_match('/^(\d+)x(\d+)$/', $v)) {
                $e = new InvalidTypeException();
                $e->addHint('Strings should use the format \'NxN\' where `N` is an integer.');

                throw $e;
            }
            $parts = explode('x', $v);

            return ['width' => (int) $parts[0], 'height' => (int) $parts[1]];
        };

        $treeBuilder->getRootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('image_dirs')
                        ->defaultValue(['%kernel.project_dir%/public/images'])
                        ->scalarPrototype()
                        ->end()
                    ->end()
                ->scalarNode('static_path')->defaultValue('%kernel.project_dir%/public/thumbs')->end()
                ->arrayNode('routing')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('mount_point')->defaultValue('/thumbs')->end()
                        ->arrayNode('image')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('controller')->defaultValue(ImageController::class)->end()
                                ->scalarNode('path')->defaultValue('{width}x{height}/{action}/{file}')->end()
                            ->end()
                        ->end()
                        ->arrayNode('image_alias')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('controller')->defaultValue(ImageAliasController::class)->end()
                                ->scalarNode('path')->defaultValue('{alias}/{file}')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('default_image')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('path')->defaultValue('image-default.png')->end()
                        ->scalarNode('filesystem')->defaultValue('camelot.image.filesystem.bundle')->end()
                    ->end()
                ->end()
                ->arrayNode('default_image_size')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('width')->defaultValue(1024)->end()
                        ->integerNode('height')->defaultValue(768)->end()
                    ->end()
                    ->beforeNormalization()->always($sizer)->end()
                ->end()
                ->arrayNode('error_image')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('path')->defaultValue('image-error.png')->end()
                        ->scalarNode('filesystem')->defaultValue('camelot.image.filesystem.bundle')->end()
                    ->end()
                ->end()
                ->scalarNode('cache_time')->defaultNull()->end()
                ->scalarNode('limit_upscaling')->defaultTrue()->end()
                ->scalarNode('only_aliases')->defaultFalse()->end()
                ->arrayNode('aliases')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('image_size')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->integerNode('width')->defaultValue(1024)->end()
                                    ->integerNode('height')->defaultValue(768)->end()
                                ->end()
                                ->beforeNormalization()->always($sizer)->end()
                            ->end()
                            ->enumNode('action')
                                ->values(['border', 'crop', 'fit', 'resize'])
                                ->beforeNormalization()
                                    ->always(fn (?string $v): string => $v ? Action::resolve($v) : Action::CROP)
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
