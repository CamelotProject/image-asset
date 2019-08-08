<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Bridge\Symfony\DependencyInjection;

use Camelot\ImageAsset\Bridge\Symfony\DependencyInjection\Configuration;
use Camelot\ImageAsset\Tests\Fixtures\Configuration\Expectation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

/**
 * @covers \Camelot\ImageAsset\Bridge\Symfony\DependencyInjection\Configuration
 */
final class ConfigurationTest extends TestCase
{
    public function testConfigTreeBuilder(): void
    {
        $config = new Configuration();
        $builder = $config->getConfigTreeBuilder();

        static::assertInstanceOf(TreeBuilder::class, $builder);
    }

    public function providerValidConfig(): iterable
    {
        yield 'Empty keys produces reference configuration values' => ['reference_empty.yaml', Expectation\ReferenceConfig::EXPECTATION];
        yield 'Reference configuration values' => ['reference.yaml', Expectation\ReferenceConfigWithAlias::EXPECTATION];
        yield 'Reference configuration values using strings' => ['reference_string_sizes.yaml', Expectation\ReferenceConfigWithAlias::EXPECTATION];
    }

    /** @dataProvider providerValidConfig */
    public function testBuildConfigFiles(string $configFile, array $expected): void
    {
        [$configs] = $this->getObjects($configFile);

        static::assertIsArray($configs);
        static::assertArrayHasKey(0, $configs);
        static::assertArrayHasKey('camelot_image_asset', $configs[0]);
    }

    public function testBuildEmptyConfig(): void
    {
        [$configs, $tree, $processor] = $this->getObjects('empty.yaml');
        $result = $processor->process($tree, [$configs[0]]);

        static::assertEquals(Expectation\ReferenceConfig::EXPECTATION, $result);
    }

    /** @dataProvider providerValidConfig */
    public function testBuildValidConfig(string $configFile, array $expected): void
    {
        [$configs, $tree, $processor] = $this->getObjects($configFile);
        $result = $processor->process($tree, [$configs[0]['camelot_image_asset']]);

        static::assertEquals($expected, $result);
    }

    public function testBuildInvalidConfig(): void
    {
        $this->expectException(InvalidTypeException::class);

        [$configs, $tree, $processor] = $this->getObjects('reference_invalid_string_sizes.yaml');
        $processor->process($tree, [$configs[0]['camelot_image_asset']]);
    }

    private function getObjects(string $configFile): array
    {
        $configDirectories = [__DIR__ . '/../../../../Fixtures/Configuration/'];

        $fileLocator = new FileLocator($configDirectories);
        $configFile = $fileLocator->locate($configFile, null, true);

        $configs = [Yaml::parse(file_get_contents($configFile))];
        $configuration = new Configuration();
        $builder = $configuration->getConfigTreeBuilder();
        $tree = $builder->buildTree();
        $processor = new Processor();

        return [$configs, $tree, $processor];
    }
}
