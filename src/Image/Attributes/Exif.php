<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Image\Attributes;

use PHPExif;
use function in_array;

/**
 * Subclassing for latitude/longitude getters.
 */
final class Exif extends PHPExif\Exif
{
    /** Casts Exif to this sub-class. */
    public static function cast(PHPExif\Exif $exif): self
    {
        $new = new static($exif->getData());
        $new->setRawData($exif->getRawData());

        return $new;
    }

    /** Returns the aspect ratio. */
    public function getAspectRatio(): float
    {
        $width = (int) $this->getWidth();
        $height = (int) $this->getHeight();
        if ($width === 0 || $height === 0) {
            return 0.0;
        }

        // Account for image rotation
        if (in_array($this->getOrientation(), [5, 6, 7, 8], true)) {
            return $height / $width;
        }

        return $width / $height;
    }

    /** Returns the latitude from the GPS data, if it exists. */
    public function getLatitude(): ?float
    {
        return $this->getGpsPart(0);
    }

    /**
     * Returns the longitude from the GPS data, if it exists.
     */
    public function getLongitude(): ?float
    {
        return $this->getGpsPart(1);
    }

    private function getGpsPart(int $index): ?float
    {
        /** @var string|false $gps */
        $gps = $this->getGPS();
        if ($gps === false) {
            return null;
        }
        $parts = array_map(function ($v) { return (float) $v; }, explode(',', $gps));

        return $parts[$index] ?? null;
    }
}
