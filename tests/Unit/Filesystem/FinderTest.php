<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Filesystem;

use Camelot\ImageAsset\Exception\BadMethodCallException;
use Camelot\ImageAsset\Filesystem\FileInterface;
use Camelot\ImageAsset\Filesystem\Filesystem;
use Camelot\ImageAsset\Filesystem\Finder;
use Camelot\ImageAsset\Filesystem\FinderInterface;
use Camelot\ImageAsset\Tests\Fixtures\App\TestKernel;
use Camelot\ImageAsset\Tests\Fixtures\Filesystem\FilesystemMockBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;
use const PHP_INT_MAX;

/**
 * @covers \Camelot\ImageAsset\Filesystem\Finder
 */
final class FinderTest extends TestCase
{
    public function testFinderObjects(): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $finder = new Finder($filesystem);

        self::assertFileInterfaceIterable($finder, 0);
    }

    public function providerResults(): iterable
    {
        yield [true, 'placeholder.jpg'];
        yield [false, 'placeholder.gif'];
    }

    /** @dataProvider providerResults */
    public function testHasResults(bool $expected, string $filename): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $finder = new Finder($filesystem);
        $finder
            ->name([$filename])
            ->notName(['*.gif'])
            ->exclude([])
            ->depth(['>0'])
        ;

        static::assertSame($expected, $finder->hasResults());
    }

    public function providerFileName(): iterable
    {
        yield ['placeholder.jpg', 'placeholder.jpg', 5, 5];
        yield ['*.svg', 'svg.svg', 2, 2];
        yield ['generic*', 'generic/generic-logo.jpg', 2, 2];
    }

    /** @dataProvider providerFileName */
    public function testFindByFileName(string $filename, string $expected, int $min, int $max = null): void
    {
        $filesystem = FilesystemMockBuilder::create()->createImages();
        $finder = new Finder($filesystem);
        $finder
            ->name([$filename])
            ->notName(['*.gif'])
            ->exclude([])
            ->depth(['>0'])
        ;

        $this->assertFileInterfaceIterable($finder, $min, $max);
        $this->assertHasFile($finder, $expected);
        $this->assertDoesNotHaveFile($finder, 'generic/generic-logo.gif');
    }

    public function providerMountPoint(): iterable
    {
        $projectDir = (new TestKernel('test', true))->getProjectDir();
        yield 'Mount on project directory, search "public/images/"' => [$projectDir, ['public/images'], 30];
        yield 'Mount on project directory, search "public/images/generic/"' => [$projectDir, ['public/images/generic'], 3, 3];
        yield 'Mount on public/images/, search "400x300"/' => ["{$projectDir}/public/images", ['400x300'], 8, 8];
    }

    /** @dataProvider providerMountPoint */
    public function testCustomMountPoint(string $mountPoint, array $searchPaths, int $min, int $max = null): void
    {
        $finder = (new Filesystem($mountPoint))
            ->finder()
            ->in($searchPaths)
        ;

        $this->assertFileInterfaceIterable($finder, $min, $max);
    }

    public function testAbsolutePathException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessageRegExp('#Can not add absolute path.+tests/Unit/Filesystem. to Camelot.ImageAsset.Filesystem.Finder::in\(\)#');

        iterator_to_array((new Filesystem(__DIR__))->finder()->in([__DIR__]));
    }

    private function assertFileInterfaceIterable(FinderInterface $finder, int $min, int $max = null): void
    {
        $found = [];
        $missing = [];
        $fail = false;
        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            if ($file instanceof FileInterface && $file->exists()) {
                $found[] = $file->getRelativePathname();
                $this->addToAssertionCount(1);
                continue;
            }
            $missing[] = $file->getRelativePathname();
            $fail = true;
        }
        $count = $finder->count();
        if ($count < $min) {
            $fail = true;
        }
        if ($max && $count > $max) {
            $fail = true;
        }
        if ($fail) {
            $message = sprintf('Finder result set does not contain all (existing) %s file objects. Expected %s <=> %s but found %s', FileInterface::class, $min, $max !== null ? $max : PHP_INT_MAX, $count) . PHP_EOL;
            $message .= (sprintf('Found:%s - %s', PHP_EOL, implode("\n - ", $found)) ?: '(nothing)') . PHP_EOL;
            if ($missing) {
                $message .= sprintf('Missing:%s%s', PHP_EOL, implode("\n - ", $missing));
            }
            static::fail($message);
        }
    }

    private function assertHasFile(Finder $finder, string $expected): void
    {
        $fail = true;
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            if ($file->getRelativePathname() === $expected) {
                $this->addToAssertionCount(1);
                $fail = false;
            }
        }
        if ($fail) {
            static::fail(sprintf('Result set is missing %s', $expected));
        }
    }

    private function assertDoesNotHaveFile(Finder $finder, string $expected): void
    {
        $fail = false;
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            if ($file->getRelativePathname() === $expected) {
                $this->addToAssertionCount(1);
                $fail = true;
            }
        }
        if ($fail) {
            static::fail(sprintf('Result set should not contain %s', $expected));
        }
    }
}
