<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Transaction;

use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Dimensions;

/**
 * @property string         $requestPath
 * @property Action         $action
 * @property Dimensions     $targetDimensions
 * @property ImageInterface $requestImage
 */
interface PhaseInterface
{
    /** Returns a hash string of this transaction. */
    public function getHash(): string;

    /** Returns the request path for this image, used for creating/saving a file path for static files. */
    public function getRequestPath(): string;

    /** Returns the request filepath. Used for finding image in filesystem. */
    public function getRequestFilePath(): string;

    public function getRequestImage(): ImageInterface;

    public function getAction(): Action;

    public function getTargetDimensions(): Dimensions;
}
