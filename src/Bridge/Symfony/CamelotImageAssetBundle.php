<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Bridge\Symfony;

use Camelot\ImageAsset\Bridge\Symfony\DependencyInjection\Compiler\AssetPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class CamelotImageAssetBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new AssetPass());
    }
}
