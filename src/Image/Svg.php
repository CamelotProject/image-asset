<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Image;

use Camelot\ImageAsset\Exception\IOException;
use Contao\ImagineSvg\Imagine as SvgImagine;
use Imagine\Exception\RuntimeException;
use Imagine\Image\ImageInterface as SvgImageInterface;

final class Svg
{
    /** Creates an Info from a string of SVG image data. */
    public static function createFromString(string $data): SvgImageInterface
    {
        try {
            $image = (new SvgImagine())->load($data);
        } catch (RuntimeException $e) {
            throw new IOException('Failed to parse image data from string', null, 0, $e);
        }

        return $image;
    }
}
