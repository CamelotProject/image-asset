<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Image;

use Camelot\ImageAsset\Exception\InvalidArgumentException;
use Camelot\ImageAsset\Exception\UnsupportedFileTypeException;
use Camelot\ImageAsset\Image\Attributes\Color;
use Camelot\ImageAsset\Image\Attributes\Info;
use Camelot\ImageAsset\Image\Attributes\Point;
use Camelot\ImageAsset\Image\Type\TypeInterface;
use Camelot\Thrower\Thrower;
use ErrorException;
use Exception;
use PHPExif\Exif;
use RuntimeException;
use function function_exists;
use function is_resource;
use const IMAGETYPE_WEBP;

/**
 * An object representation of GD's native image resources.
 */
final class ImageResource
{
    /**
     * Quality setting for JPEGs and PNGs
     *
     * @var int
     */
    private static $quality = 80;
    /** @var bool */
    private static $normalizeJpegOrientation = true;

    /** @var resource */
    private $resource;
    /** @var ?TypeInterface */
    private $type;
    /** @var ?Info */
    private $info;

    public function __construct($resource, TypeInterface $type = null, Info $info = null)
    {
        if (!is_resource($resource) || get_resource_type($resource) !== 'gd') {
            throw new InvalidArgumentException('Given resource must be a GD resource');
        }

        $this->resource = $resource;
        $this->type = $type;
        $this->info = $info;

        if ($info) {
            $this->type = $info->getType();
        }
        if ($this->type === null) {
            throw new InvalidArgumentException('Type or ImageInfo needs to be provided');
        }

        if ($this->type->getId() === IMAGETYPE_JPEG && static::$normalizeJpegOrientation) {
            $this->normalizeJpegOrientation();
        }
    }

    /** @codeCoverageIgnore */
    public function __toString(): string
    {
        try {
            return $this->toString();
        } catch (Exception $e) {
            return '';
        }
    }

    public function __clone()
    {
        $original = $this->resource;

        $dim = $this->getDimensions();
        $copy = static::createNew($dim, $this->getType());
        imagecopy($copy->resource, $original, 0, 0, 0, 0, $dim->getWidth(), $dim->getHeight());

        $this->resource = $copy->resource;

        $this->resetInfo();
    }

    /** @codeCoverageIgnore */
    public function __destroy(): void
    {
        imagedestroy($this->resource);
    }

    /**
     * Creates an ImageResource from a file.
     */
    public static function createFromFile(string $filePath): self
    {
        $info = Info::createFromFile($filePath);
        $imageType = $info->getType()->getId();
        $map = [
            IMAGETYPE_BMP => 'imagecreatefromwbmp',
            IMAGETYPE_GIF => 'imagecreatefromgif',
            IMAGETYPE_JPEG => 'imagecreatefromjpeg',
            IMAGETYPE_PNG => 'imagecreatefrompng',
            IMAGETYPE_WEBP => 'imagecreatefromwebp',
        ];
        if (!isset($map[$imageType]) || !function_exists($map[$imageType])) {
            throw new UnsupportedFileTypeException(image_type_to_mime_type($imageType), $filePath);
        }

        try {
            $resource = Thrower::call($map[$imageType], $filePath);
        } catch (ErrorException $e) {
            throw new UnsupportedFileTypeException(image_type_to_mime_type($imageType), $filePath, 0, $e);
        }

        return new static($resource, null, $info);
    }

    /**
     * Creates an ImageResource from a string of image data.
     *
     * @param string $data A string containing the image data
     *
     * @codeCoverageIgnore
     */
    public static function createFromString(string $data): self
    {
        $info = Info::createFromString($data);

        try {
            $resource = Thrower::call('imagecreatefromstring', $data);
        } catch (ErrorException $e) {
            throw new InvalidArgumentException('Invalid image data', 0, $e);
        }

        return new static($resource, null, $info);
    }

    /**
     * Creates a new image given the width and height.
     *
     * @codeCoverageIgnore
     */
    public static function createNew(Dimensions $dimensions, ?TypeInterface $type): self
    {
        try {
            $resource = Thrower::call('imagecreatetruecolor', $dimensions->getWidth(), $dimensions->getHeight());
        } catch (ErrorException $e) {
            throw new InvalidArgumentException('Failed to create new image');
        }

        // Preserve transparency
        imagealphablending($resource, false);
        imagesavealpha($resource, true);

        return new static($resource, $type);
    }

    /**
     * Returns the GD resource.
     *
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Returns the image dimensions.
     */
    public function getDimensions(): Dimensions
    {
        return new Dimensions(imagesx($this->resource), imagesy($this->resource));
    }

    /**
     * Returns the image type.
     */
    public function getType(): ?TypeInterface
    {
        return $this->type;
    }

    /**
     * Returns the image's info.
     */
    public function getInfo(): Info
    {
        if (!$this->info) {
            $this->info = Info::createFromString($this->toString());
        }

        return $this->info;
    }

    public function getExif(): Exif
    {
        return $this->getInfo()->getExif();
    }

    /**
     * Allocate a color for an image.
     *
     * @param int      $red   Value of red component (between 0 and 255)
     * @param int      $green Value of green component (between 0 and 255)
     * @param int      $blue  Value of blue component (between 0 and 255)
     * @param int|null $alpha Optional value of alpha component (between 0 and 127). 0 = opaque, 127 = transparent.
     */
    public function allocateColor(int $red, int $green, int $blue, ?int $alpha = null): Color
    {
        // Verify parameters before trying to allocate
        new Color($red, $green, $blue, $alpha);

        // Reuse same color if its already in index
        if ($alpha === null) {
            $index = imagecolorexact($this->resource, $red, $green, $blue);
        } else {
            $index = imagecolorexactalpha($this->resource, $red, $green, $blue, $alpha);
        }
        if ($index !== -1) {
            return new Color($red, $green, $blue, $alpha, $index);
        }

        // Allocate new color
        // @codeCoverageIgnoreStart
        if ($alpha === null) {
            $index = imagecolorallocate($this->resource, $red, $green, $blue);
        } else {
            $index = imagecolorallocatealpha($this->resource, $red, $green, $blue, $alpha);
        }
        if ($index === false) {
            throw new InvalidArgumentException('Failed to create color');
        }

        return new Color($red, $green, $blue, $alpha, $index);
        // @codeCoverageIgnoreEnd
    }

    /** Allocate a transparent color for an image. */
    public function allocateTransparentColor(): Color
    {
        // Reuse same transparent color index if it exists
        $index = imagecolortransparent($this->resource);
        if ($index === -1) {
            // ok allocate it
            $color = $this->allocateColor(0, 0, 0, 127);
            $index = imagecolortransparent($this->resource, (int) $color->getIndex());
        }

        return new Color(0, 0, 0, 127, $index);
    }

    /** Returns the color at a point. */
    public function getColorAt(Point $point): Color
    {
        $dim = $this->getDimensions();
        if ($point->getX() > $dim->getWidth() || $point->getY() > $dim->getHeight()) {
            throw new InvalidArgumentException(
                "Given coordinates ({$point->getX()}, {$point->getY()}) are out of bounds"
            );
        }

        $index = imagecolorat($this->resource, $point->getX(), $point->getY());
        $rgb = imagecolorsforindex($this->resource, $index);

        return new Color($rgb['red'], $rgb['green'], $rgb['blue'], $rgb['alpha'], $index);
    }

    /**
     * Flood fill.
     *
     * @param Color $color      The fill color
     * @param Point $startPoint The point to start at
     */
    public function fill(Color $color, Point $startPoint = null): self
    {
        $startPoint = $startPoint ?: new Point();
        $color = $this->verifyColor($color);
        imagefill($this->resource, $startPoint->getX(), $startPoint->getY(), (int) $color->getIndex());

        return $this;
    }

    /**
     * Resize part of an image with resampling.
     *
     * @param Point         $destPoint      The destination point
     * @param Point         $srcPoint       The source point
     * @param Dimensions    $destDimensions The destination dimensions
     * @param Dimensions    $srcDimensions  The source dimensions
     * @param ImageResource $dest           Optional destination image. Default is current image.
     */
    public function resample(
        Point $destPoint,
        Point $srcPoint,
        Dimensions $destDimensions,
        Dimensions $srcDimensions,
        self $dest = null
    ): self {
        $dest = $dest ?: clone $this;

        imagecopyresampled(
            $dest->resource,
            $this->resource,
            $destPoint->getX(),
            $destPoint->getY(),
            $srcPoint->getX(),
            $srcPoint->getY(),
            $destDimensions->getWidth(),
            $destDimensions->getHeight(),
            $srcDimensions->getWidth(),
            $srcDimensions->getHeight()
        );
        $this->resource = $dest->resource;
        $this->resetInfo();

        return $this;
    }

    /**
     * Flips the image ('V' = vertical, 'H' = horizontal, 'HV' = both).
     *
     * Based on http://stackoverflow.com/a/10001884/1136593
     * Thanks Jon Grant
     */
    public function flip(string $mode): self
    {
        $dim = $this->getDimensions();

        $srcPoint = new Point();
        $srcDim = clone $dim;

        // Flip vertically
        if (stripos($mode, 'V') !== false) {
            $srcPoint->setY($dim->getHeight() - 1);
            $srcDim->setHeight(-$dim->getHeight());
        }

        // Flip horizontally
        if (stripos($mode, 'H') !== false) {
            $srcPoint->setX($dim->getWidth() - 1);
            $srcDim->setWidth(-$dim->getWidth());
        }

        $this->resample(new Point(), $srcPoint, $dim, $srcDim);

        return $this;
    }

    /**
     * Rotates the image.
     *
     * @param string $angle ('L' = -90°, 'R' = +90°, 'T' = 180°)
     */
    public function rotate($angle): self
    {
        $rotate = [
            'L' => 270,
            'R' => 90,
            'T' => 180,
        ];

        if (!isset($rotate[$angle])) {
            return $this;
        }

        $this->resource = imagerotate($this->resource, $rotate[$angle], 0);
        $this->resetInfo();

        return $this;
    }

    /** Writes the image to a file. */
    public function toFile(?string $filePath): void
    {
        $imageType = $this->type->getId();
        switch ($imageType) {
            case IMAGETYPE_BMP:
                imagewbmp($this->resource, $filePath);
                break;
            case IMAGETYPE_GIF:
                imagegif($this->resource, $filePath);
                break;
            case IMAGETYPE_JPEG:
                imageinterlace($this->resource, 1);
                imagejpeg($this->resource, $filePath, static::$quality);
                break;
            case IMAGETYPE_PNG:
                $compression = static::convertJpegQualityToPngCompression(static::$quality);
                imagepng($this->resource, $filePath, $compression);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($this->resource, $filePath);
                break;
            default:
                throw new UnsupportedFileTypeException(image_type_to_mime_type($imageType), $filePath); // @codeCoverageIgnore
        }
    }

    /**
     * Returns the image as a data string.
     *
     * @codeCoverageIgnore
     */
    public function toString(): string
    {
        ob_start();

        try {
            $this->toFile(null);
        } catch (RuntimeException $e) {
            ob_end_clean();
            throw $e;
        }

        $data = ob_get_contents();
        ob_end_clean();

        return $data;
    }

    /** Returns whether JPEG orientation is normalized or not. */
    public static function isJpegOrientationNormalized(): bool
    {
        return static::$normalizeJpegOrientation;
    }

    /** Enable or disable JPEG orientation normalization. */
    public static function setNormalizeJpegOrientation(bool $normalizeJpegOrientation): void
    {
        static::$normalizeJpegOrientation = $normalizeJpegOrientation;
    }

    /**
     * Sets the quality setting.
     *
     * Note: A quality < 10 is assumed to be PNG compression scale.
     *
     * @param int $quality Between 0 and 100
     */
    public static function setQuality(int $quality): void
    {
        if ($quality < 0 || $quality > 100) {
            throw new InvalidArgumentException('Quality is expected to be between 0 and 100');
        }
        if ($quality < 10) {
            // assume PNG scale
            $quality = static::convertPngCompressionToJpegQuality($quality);
        }
        static::$quality = $quality;
    }

    /** Returns the quality setting. */
    public static function getQuality(): int
    {
        return static::$quality;
    }

    /**
     * Convert JPEG quality scale to PNG compression scale.
     * JPEG: 0 (worst) - 100 (best). PNG: 0 (best) - 10 (worst).
     */
    private static function convertJpegQualityToPngCompression(int $quality): int
    {
        $quality = (100 - $quality) / 10;
        $quality = min(ceil($quality), 9);

        return (int) $quality;
    }

    /** Convert PNG compression scale to JPEG quality scale. */
    private static function convertPngCompressionToJpegQuality(int $compression): int
    {
        return 100 - (10 * $compression);
    }

    /**
     * If orientation in EXIF data is not normal,
     * flip and/or rotate image until it is correct.
     */
    private function normalizeJpegOrientation(): void
    {
        $orientation = $this->getExif()->getOrientation();
        $modes = [2 => 'H-', 3 => '-T', 4 => 'V-', 5 => 'VL', 6 => '-L', 7 => 'HL', 8 => '-R'];
        if (!isset($modes[$orientation])) {
            return;
        }
        $mode = $modes[$orientation];

        $this->flip($mode[0])->rotate($mode[1]);
    }

    /** Verifies that a color is allocated. */
    private function verifyColor(Color $color): Color
    {
        if ($color->getIndex() !== null) {
            return $color;
        }

        return $this->allocateColor($color->getRed(), $color->getGreen(), $color->getBlue(), $color->getAlpha());
    }

    /** If image changes, info needs to be recreated. */
    private function resetInfo(): void
    {
        $this->info = null;
    }
}
