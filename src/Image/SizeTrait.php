<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Image;

trait SizeTrait
{
    abstract public function getSize(): int;

    /** {@inheritdoc} */
    public function getSizeFormatted(bool $si = false): string
    {
        $size = $this->getSize();

        if ($si) {
            return $this->getSizeFormattedSi($size);
        }

        return $this->getSizeFormattedExact($size);
    }

    /** Format a file's size according to IEC standard. For example: '4734 bytes' -> '4.62 KiB'. */
    private function getSizeFormattedExact(int $size): string
    {
        if ($size > 1024 * 1024) {
            return sprintf('%0.2f MiB', ($size / 1024 / 1024));
        }
        if ($size > 1024) {
            return sprintf('%0.2f KiB', ($size / 1024));
        }

        return $size . ' B';
    }

    /**
     * Format a filesize as 'end user friendly', so this should be seen as something that'd
     * be used in a quick glance. For example: '4734 bytes' -> '4.7 kB'.
     */
    private function getSizeFormattedSi(int $size): string
    {
        if ($size > 1000 * 1000) {
            return sprintf('%0.1f MB', ($size / 1000 / 1000));
        }
        if ($size > 1000) {
            return sprintf('%0.1f KB', ($size / 1000));
        }

        return $size . ' B';
    }
}
