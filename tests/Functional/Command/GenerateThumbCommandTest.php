<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Functional\Command;

use Camelot\ImageAsset\Exception\RuntimeException;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use Camelot\ImageAsset\Tests\Functional\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group  functional
 * @covers \Camelot\ImageAsset\Command\GenerateThumbCommand
 */
final class GenerateThumbCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);
        $builder = FilesystemMockBuilder::create();

        $command = $application->find('camelot:image:generate-thumb');
        $commandTester = new CommandTester($command);
        $result = $commandTester->execute([
            'command' => $command->getName(),
            'source' => 'public/images/default.png',
            'destination' => $builder->createThumbs()->getMountPath() . '/default.png',
            'width' => 200,
            'height' => 150,
            'action' => 'crop',
        ]);

        static::assertIsInt($result);
    }

    public function testExecuteInputFileDoesNotExist(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File not found "invalid.ext"');

        $kernel = static::createKernel();
        $application = new Application($kernel);
        $filesystem = FilesystemMockBuilder::create();

        $command = $application->find('camelot:image:generate-thumb');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'source' => 'invalid.ext',
            'destination' => $filesystem->createThumbs()->getMountPath() . '/invalid.ext',
            'width' => 200,
            'height' => 150,
            'action' => 'crop',
        ]);
    }

    // Yeah, I got a giggle when I saw what I named this one :)
    public function providerForce(): iterable
    {
        yield 'Overwrite' => [true];
        yield 'Throw exception' => [false];
    }

    /** @dataProvider providerForce */
    public function testExecuteExistingDestinationHandling(bool $force): void
    {
        if (!$force) {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessageRegExp('#File .+foo.png exists. Refusing to overwrite#');
        }

        $kernel = static::createKernel();
        $application = new Application($kernel);
        $builder = FilesystemMockBuilder::create();

        $command = $application->find('camelot:image:generate-thumb');
        $commandTester = new CommandTester($command);
        $input = [
            'command' => $command->getName(),
            'source' => 'public/images/default.png',
            'destination' => $builder->createThumbs()->getMountPath() . '/foo.png',
            'width' => 200,
            'height' => 150,
            'action' => 'crop',
            '--force' => $force,
        ];

        $commandTester->execute($input);
        $commandTester->execute($input);

        if ($force) {
            $this->addToAssertionCount(1);
        }
    }
}
