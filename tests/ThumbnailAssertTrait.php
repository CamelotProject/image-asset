<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests;

use Camelot\ImageAsset\Image\Attributes\Info;
use Camelot\ImageAsset\Image\Dimensions;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use function is_string;
use function strlen;
use const DIRECTORY_SEPARATOR;

/**
 * @method void addToAssertionCount(int $count)
 * @method void fail(string $message)
 */
trait ThumbnailAssertTrait
{
    protected function assertThumbnailLocation(string $savePath, string $expectedRelative): void
    {
        $expected = '/' . implode('/', array_filter(explode('/', sprintf('%s/%s', $savePath, $expectedRelative))));
        if (file_exists($expected)) {
            $this->addToAssertionCount(1);

            return;
        }
        $finder = new Finder();
        $finder
            ->files()
            ->in($_SERVER['APP_SAVE_PATH'])
        ;
        $this->fail(sprintf(
            'Did not find thumbnail file in expected location: %s%s%s' . PHP_EOL . 'Found:%s',
            $_SERVER['APP_SAVE_PATH'],
            DIRECTORY_SEPARATOR,
            $expectedRelative,
            array_reduce(iterator_to_array($finder->getIterator()), function ($i, SplFileInfo $v) {
                return "\n  - " . $_SERVER['APP_SAVE_PATH'] . DIRECTORY_SEPARATOR . $v->getRelativePathname();
            })
        ));
    }

    /**
     * @param Dimensions|string $actual
     */
    private function assertDimensions(Dimensions $expected, $actual): void
    {
        if (is_string($actual)) {
            $info = Info::createFromString($actual);
            $actual = new Dimensions($info->getWidth(), $info->getHeight());
        }
        $this->assertEquals($expected, $actual, "Expected dimension $expected does not equal actual $actual");
    }

    private function assertThumbnailsSame(string $expected, string $content): void
    {
        if ($content !== $expected) {
            $this->fail(sprintf('Data returned does not match expected image.%s- Size: %s B%s- Magic: %s', PHP_EOL, number_format(strlen($content)), PHP_EOL, substr($content, 0, 7)));
        } else {
            $this->addToAssertionCount(1);
        }
    }
}
