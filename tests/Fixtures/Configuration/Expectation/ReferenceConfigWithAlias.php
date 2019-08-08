<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Fixtures\Configuration\Expectation;

use Camelot\ImageAsset\Controller\ImageAliasController;
use Camelot\ImageAsset\Controller\ImageController;

final class ReferenceConfigWithAlias
{
    public const EXPECTATION = [
        'image_dirs' => ['%kernel.project_dir%/public/images'],
        'static_path' => '%kernel.project_dir%/public/thumbs',
        'routing' => [
            'mount_point' => '/thumbs',
            'image' => [
                'controller' => ImageController::class,
                'path' => '{width}x{height}/{action}/{file}',
            ],
            'image_alias' => [
                'controller' => ImageAliasController::class,
                'path' => '{alias}/{file}',
            ],
        ],
        'default_image' => [
            'path' => 'image-default.png',
            'filesystem' => 'camelot.image.filesystem.bundle',
        ],
        'default_image_size' => [
            'width' => 1024,
            'height' => 768,
        ],
        'error_image' => [
            'path' => 'image-error.png',
            'filesystem' => 'camelot.image.filesystem.bundle',
        ],
        'cache_time' => null,
        'limit_upscaling' => true,
        'only_aliases' => false,
        'aliases' => [
            '1024x768c' => [
                'image_size' => [
                    'width' => 1024,
                    'height' => 768,
                ],
                'action' => 'crop',
            ],
        ],
    ];
}
