<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Transaction;

use Camelot\ImageAsset\Exception\BadMethodCallException;
use Camelot\ImageAsset\Exception\RuntimeException;
use Camelot\ImageAsset\Filesystem\ImageInterface;
use Camelot\ImageAsset\Image\Attributes\Action;
use Camelot\ImageAsset\Image\Dimensions;
use ReflectionClass;
use ReflectionProperty;

trait PhaseTrait
{
    /** @var string */
    private $requestPath;
    /** @var ImageInterface */
    private $requestImage;
    /** @var Action */
    private $action;
    /** @var Dimensions */
    private $targetDimensions;

    /** @internal */
    public static function create(?PhaseInterface $oldPhase = null, iterable $args = []): self
    {
        $transposeObject = function (PhaseInterface $oldPhase, PhaseInterface $newPhase, string $property): void {
            $rp = new ReflectionProperty($oldPhase, $property);
            $rp->setAccessible(true);
            $newPhase->{$property} = $rp->getValue($oldPhase);
        };

        $transposeValues = function (PhaseInterface $newPhase, string $property, $value): void {
            if (!property_exists($newPhase, $property)) {
                throw new RuntimeException(sprintf('%s does not have the property %s', __CLASS__, $property));
            }
            $newPhase->{$property} = $value;
        };

        /** @var PhaseInterface|self $newPhase */
        $newPhase = (new ReflectionClass(__CLASS__))->newInstanceWithoutConstructor();

        self::assertCorrectTransition($oldPhase, $newPhase);

        if ($oldPhase) {
            foreach (['requestPath', 'action', 'targetDimensions', 'requestImage'] as $property) {
                $transposeObject($oldPhase, $newPhase, $property);
            }
            foreach ($args as $property => $value) {
                $transposeValues($newPhase, $property, $value);
            }
        }

        return $newPhase;
    }

    public function getHash(): string
    {
        $path = str_replace('/', '_', $this->getRequestFilePath());

        return implode('-', [$path, $this->action, $this->targetDimensions->getWidth(), $this->targetDimensions->getHeight()]);
    }

    public function getRequestFilePath(): string
    {
        return $this->requestImage->getRelativePathname();
    }

    public function getRequestPath(): string
    {
        return $this->requestPath;
    }

    public function getRequestImage(): ImageInterface
    {
        return $this->requestImage;
    }

    public function getAction(): Action
    {
        return $this->action;
    }

    public function getTargetDimensions(): Dimensions
    {
        return $this->targetDimensions;
    }

    /** {@inheritdoc} */
    private static function assertCorrectTransition(?PhaseInterface $oldPhase, PhaseInterface $newPhase): void
    {
        if ($newPhase instanceof RequisitionInterface && $oldPhase !== null) {
            throw new BadMethodCallException(sprintf('Cannot create a %s with any previous transaction objects.', RequisitionInterface::class));
        }
        if ($newPhase instanceof JobInterface && !$oldPhase instanceof RequisitionInterface) {
            throw new BadMethodCallException(sprintf('Cannot create a %s without a %s object.', JobInterface::class, RequisitionInterface::class));
        }
    }
}
