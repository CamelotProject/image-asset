<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Bridge\Symfony\Mime;

use Camelot\ImageAsset\Image\Type\SvgType;
use Symfony\Component\Mime\MimeTypes;

/**
 * @internal
 */
final class MimeTypeGuesserFactory
{
    public static function create(): MimeTypes
    {
        $mimeTypes = (new MimeTypes());
        $mimeTypes->registerGuesser(new BinaryMimeTypeGuesser());

        return $mimeTypes;
    }

    /** Determine data string is an SVG image. */
    public static function isSvg(string $data, ?string $filename): bool
    {
        $mimeType = self::create()->guessMimeType((string) $filename ?: $data);

        return strpos(SvgType::MIME, $mimeType) === 0;
    }
}
