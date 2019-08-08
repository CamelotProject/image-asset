<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Bridge\Symfony;

use Camelot\ImageAsset\Bridge\Symfony\CamelotImageAssetBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @cover \Camelot\ImageAsset\Bridge\Symfony\CamelotImageAssetBundle
 */
final class CamelotImageAssetBundleTest extends TestCase
{
    public function testBuild(): void
    {
        $mock = $this->createMock(ContainerBuilder::class);
        $mock
            ->expects(static::atLeastOnce())
            ->method('addCompilerPass')
        ;
        (new CamelotImageAssetBundle())->build($mock);
    }
}
