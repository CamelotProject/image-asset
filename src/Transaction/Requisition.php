<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Transaction;

use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Dimensions;

/**
 * A storage entity for a thumbnail creation request.
 */
final class Requisition implements RequisitionInterface
{
    use PhaseTrait;

    /** @codeCoverageIgnore */
    private function __construct()
    {
    }

    public function setRequestPath(string $requestPath): RequisitionInterface
    {
        $this->requestPath = $requestPath;

        return $this;
    }

    public function setAction(Action $action): RequisitionInterface
    {
        $this->action = $action;

        return $this;
    }

    public function setRequestImage(ImageInterface $requestImage): RequisitionInterface
    {
        $this->requestImage = $requestImage;

        return $this;
    }

    public function setTargetDimensions(Dimensions $targetDimensions): RequisitionInterface
    {
        $this->targetDimensions = $targetDimensions;

        return $this;
    }
}
