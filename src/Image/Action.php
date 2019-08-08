<?php

declare(strict_types=1);

namespace Camelot\ImageAssets\Image;

/**
 * Actions used when creating thumbnails.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
final class Action
{
    public const CROP = 'crop';
    public const RESIZE = 'resize';
    public const BORDER = 'border';
    public const FIT = 'fit';

    /**
     * Singleton constructor.
     */
    private function __construct()
    {
    }
}
