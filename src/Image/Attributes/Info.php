<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Image\Attributes;

use Camelot\ImageAsset\Bridge\Symfony\Mime\MimeTypeGuesserFactory;
use Camelot\ImageAsset\Image\Dimensions;
use Camelot\ImageAsset\Image\Svg;
use Camelot\ImageAsset\Image\Type\SvgType;
use Camelot\ImageAsset\Image\Type\Type;
use Camelot\ImageAsset\Image\Type\TypeInterface;
use Camelot\Thrower\Thrower;
use ErrorException;
use JsonSerializable;
use PHPExif\Reader\Reader;
use Serializable;
use function in_array;

/**
 * An object representation of properties returned from getimagesize() and EXIF data.
 */
final class Info implements JsonSerializable, Serializable
{
    /** @var ?ReaderInterface */
    private static $exifReader = null;

    /** @var Dimensions */
    private $dimensions;
    /** @var TypeInterface */
    private $type;
    /** @var int */
    private $bits;
    /** @var int */
    private $channels;
    /** @var ?string */
    private $mime;
    /** @var Exif */
    private $exif;
    /** @var bool */
    private $valid;

    public function __construct(Dimensions $dimensions, TypeInterface $type, int $bits, int $channels, ?string $mime, Exif $exif)
    {
        $this->dimensions = $dimensions;
        $this->type = $type;
        $this->bits = $bits;
        $this->channels = $channels;
        $this->mime = $mime;
        $this->exif = $exif;
        $this->valid = true;
    }

    /** {@inheritdoc} */
    public function __clone()
    {
        $this->exif = clone $this->exif;
    }

    /**
     * Creates an empty, invalid Info. Useful to prevent null checks for non-existent or invalid images.
     */
    public static function createInvalid(): self
    {
        $invalid = new static(new Dimensions(0, 0), Type::unknown(), 0, 0, null, new Exif([]));
        $invalid->valid = false;

        return $invalid;
    }

    /**
     * Creates an Info from a file.
     *
     * @param string $file A filepath
     */
    public static function createFromFile(string $file): self
    {
        try {
            $info = Thrower::call('getimagesize', $file);
        } catch (ErrorException $e) {
            return static::createInvalid();
        }
        if ($info) {
            return static::createFromArray($info, static::readExif($file));
        }
        $data = Thrower::call('file_get_contents', $file);
        if (MimeTypeGuesserFactory::isSvg($data, $file)) {
            return static::createSvgFromString($data);
        }

        return static::createInvalid(); // @codeCoverageIgnore
    }

    /**
     * Creates an Info from a string of image data.
     *
     * @param string      $data     A string containing the image data
     * @param null|string $filename the filename used for determining the MIME Type
     */
    public static function createFromString(string $data, ?string $filename = null): self
    {
        if ($data === '') {
            return static::createInvalid();
        }
        if (MimeTypeGuesserFactory::isSvg($data, $filename)) {
            return static::createSvgFromString($data);
        }

        try {
            $info = Thrower::call('getimagesizefromstring', $data);
        } catch (ErrorException $e) {
            return static::createInvalid();
        }

        return static::createFromArray($info, static::readExif(sprintf('data://%s;base64,%s', $info['mime'], base64_encode($data))));
    }

    /** Creates info from a previous json serialized object. */
    public static function createFromJson(array $data): self
    {
        return new static(
            new Dimensions($data['dims'][0], $data['dims'][1]),
            Type::getById($data['type']),
            $data['bits'],
            $data['channels'],
            $data['mime'],
            new Exif($data['exif'])
        );
    }

    /**
     * Returns the image's dimensions.
     */
    public function getDimensions(): Dimensions
    {
        return $this->dimensions;
    }

    /**
     * Returns the image width.
     */
    public function getWidth(): int
    {
        return $this->dimensions->getWidth();
    }

    /**
     * Returns the image height.
     */
    public function getHeight(): int
    {
        return $this->dimensions->getHeight();
    }

    /** Returns the aspect ratio. */
    public function getAspectRatio(): float
    {
        if ($this->getWidth() === 0 || $this->getHeight() === 0) {
            return 0.0;
        }

        // Account for image rotation
        if (in_array($this->exif->getOrientation(), [5, 6, 7, 8], true)) {
            return $this->getHeight() / $this->getWidth();
        }

        return $this->getWidth() / $this->getHeight();
    }

    /**
     * Returns whether or not the image is landscape.
     *
     * This is determined by the aspect ratio being
     * greater than 5:4.
     */
    public function isLandscape(): bool
    {
        return $this->getAspectRatio() >= 1.25;
    }

    /**
     * Returns whether or not the image is portrait.
     *
     * This is determined by the aspect ratio being
     * less than 4:5.
     */
    public function isPortrait(): bool
    {
        return $this->getAspectRatio() <= 0.8;
    }

    /**
     * Returns whether or not the image is square-ish.
     *
     * The image is considered square if it is not
     * determined to be landscape or portrait.
     */
    public function isSquare(): bool
    {
        return !$this->isLandscape() && !$this->isPortrait();
    }

    /**
     * Returns the image type.
     */
    public function getType(): TypeInterface
    {
        return $this->type;
    }

    /** Returns the number of bits for each color. */
    public function getBits(): int
    {
        return $this->bits;
    }

    /**
     * Returns the number of channels or colors.
     *
     * 3 for RGB and 4 for CMYK.
     */
    public function getChannels(): int
    {
        return $this->channels;
    }

    /** Returns the image's MIME type. */
    public function getMime(): ?string
    {
        return $this->mime;
    }

    /** Returns the image's EXIF data. */
    public function getExif(): Exif
    {
        return $this->exif;
    }

    /** Whether this Info is valid or if there was an error. */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @return       (array|bool|string|null|int)[]
     *
     * @psalm-return array{dims: array{0: int, 1: int}, type: int, bits: int, channels: int, mime: string|null, exif:
     *               array, valid: bool}
     */
    public function jsonSerialize(): array
    {
        return [
            'dims' => [$this->dimensions->getWidth(), $this->dimensions->getHeight()],
            'type' => $this->type->getId(),
            'bits' => $this->bits,
            'channels' => $this->channels,
            'mime' => $this->mime,
            'exif' => $this->exif->getData(),
            'valid' => $this->valid,
        ];
    }

    /** {@inheritdoc} */
    public function serialize(): string
    {
        return serialize($this->jsonSerialize());
    }

    /** {@inheritDoc} */
    public function unserialize($serialized): void
    {
        $data = unserialize($serialized);

        $this->dimensions = new Dimensions($data['dims'][0], $data['dims'][1]);
        $this->type = Type::getById($data['type']);
        $this->bits = $data['bits'];
        $this->channels = $data['channels'];
        $this->mime = $data['mime'];
        $this->exif = new Exif($data['exif']);
        $this->valid = $data['valid'];
    }

    private static function createFromArray(array $info, Exif $exif): self
    {
        // Add defaults to skip isset checks
        $info += [
            0 => 0,
            1 => 0,
            2 => 0,
            'bits' => 0,
            'channels' => 0,
            'mime' => '',
        ];

        return new static(
            new Dimensions($info[0], $info[1]),
            Type::getById((int) $info[2]),
            $info['bits'],
            $info['channels'],
            $info['mime'],
            $exif
        );
    }

    /**
     * Creates an Info from a string of SVG image data.
     */
    private static function createSvgFromString(string $data): self
    {
        $image = Svg::createFromString($data);

        $box = $image->getSize();
        $dimensions = new Dimensions($box->getWidth(), $box->getHeight());

        return new static(
            $dimensions,
            Type::getById(SvgType::ID),
            0,
            0,
            SvgType::MIME,
            new Exif([])
        );
    }

    private static function readExif(string $file): Exif
    {
        if (static::$exifReader === null) {
            static::$exifReader = Reader::factory(Reader::TYPE_NATIVE); // @codeCoverageIgnore
        }

        $exif = static::$exifReader->read($file);
        if ($exif instanceof \PHPExif\Exif) {
            return Exif::cast($exif);
        }

        return new Exif();
    }
}
