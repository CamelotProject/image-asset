<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Transaction;

use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Dimensions;

/**
 * A storage entity for a thumbnail creation request.
 */
interface RequisitionInterface extends PhaseInterface
{
    public function setRequestPath(string $requestPath): self;

    public function setAction(Action $action): self;

    public function setTargetDimensions(Dimensions $targetDimensions): self;

    public function setRequestImage(ImageInterface $requestImage): self;
}
