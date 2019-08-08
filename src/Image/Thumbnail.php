<?php

declare(strict_types=1);

namespace Camelot\ImageAssets\Image;

use Camelot\Filesystem\Handler\Image;

/**
 * Stores thumbnail data and image file.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class Thumbnail
{
    /** @var Image */
    protected $image;
    /** @var string */
    protected $thumbnail;

    /**
     * Thumbnail constructor.
     *
     * @param string $thumbnail
     */
    public function __construct(Image $image, $thumbnail)
    {
        $this->image = $image;
        $this->thumbnail = $thumbnail;
    }

    public function __toString()
    {
        return $this->thumbnail;
    }

    /**
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return string
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }
}
