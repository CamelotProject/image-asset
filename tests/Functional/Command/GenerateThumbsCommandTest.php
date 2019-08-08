<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Functional\Command;

use Camelot\ImageAsset\Exception\RuntimeException;
use Camelot\ImageAsset\Filesystem\Filesystem;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use Camelot\ImageAsset\Tests\Functional\KernelTestCase;
use Camelot\ImageAsset\Tests\ThumbnailAssertTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use const DIRECTORY_SEPARATOR;

/**
 * @group  functional
 * @covers \Camelot\ImageAsset\Command\GenerateThumbsCommand
 */
final class GenerateThumbsCommandTest extends KernelTestCase
{
    use ThumbnailAssertTrait;

    /** @var string */
    private $mountPath;
    /** @var string */
    private $saveSubPath;
    /** @var Filesystem */
    private $imageFilesystem;

    protected function setUp(): void
    {
        parent::setUp();

        $builder = FilesystemMockBuilder::create();
        $this->mountPath = $builder->createThumbs()->getMountPath();
        $this->imageFilesystem = $builder->createImages();
        $this->saveSubPath = '100x200.crop.images';
    }

    public function testExecute(): string
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('camelot:image:generate-thumbs');
        $commandTester = new CommandTester($command);
        $result = $commandTester->execute([
            'command' => $command->getName(),
            'aliases' => 'test_100x200',
            '--include' => 'public/images/generic',
        ]);
        static::assertIsInt($result);

        return $commandTester->getDisplay();
    }

    public function testExecuteInvalidInclude(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Can not add an absolute path to include/exclude');

        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('camelot:image:generate-thumbs');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'aliases' => 'test_100x200',
            '--include' => '/root/zomg',
        ]);
    }

    /**
     * @depends testExecute
     */
    public function testOutput(string $output): void
    {
        static::assertRegExp('#PASS[ ]+images.generic.generic-logo.gif[ ]+' . $this->saveSubPath . '.generic.generic-logo.gif#', $output);
        static::assertRegExp('#PASS[ ]+images.generic.generic-logo.png[ ]+' . $this->saveSubPath . '.generic.generic-logo.png#', $output);
        static::assertRegExp('#PASS[ ]+images.generic.generic-logo.jpg[ ]+' . $this->saveSubPath . '.generic.generic-logo.jpg#', $output);
    }

    public function providerOutputFiles(): iterable
    {
        yield 'image-error.png' => ['error.png'];
        yield 'image-default.png' => ['default.png'];
        yield 'generic/generic-logo.jpg' => ['generic/generic-logo.jpg'];
        yield 'generic/generic-logo.gif' => ['generic/generic-logo.gif'];
        yield 'generic/generic-logo.png' => ['generic/generic-logo.png'];
        yield 'placeholder-128x128b.jpg' => ['placeholder-128x128b.jpg'];
    }

    /**
     * @depends      testExecute
     * @dataProvider providerOutputFiles
     */
    public function testFilesAreOutputToCorrectLocation(string $sourceFilePath): void
    {
        static::markTestIncomplete();

        $this->assertThumbnailLocation($this->mountPath . DIRECTORY_SEPARATOR . $this->saveSubPath, $sourceFilePath);
    }
}
