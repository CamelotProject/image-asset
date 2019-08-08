<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Thumbnail;

use Camelot\ImageAsset\Transaction\JobInterface;

final class Rescaler
{
    /** @var bool */
    private $limitUpscaling;

    public function __construct(bool $limitUpscaling)
    {
        $this->limitUpscaling = $limitUpscaling;
    }

    /** If target width and/or height are set to 0, they are set based on the image's height/width. */
    public function autoscale(JobInterface $job): void
    {
        $this->checkForUpscale($job);
        $info = $job->getRequestImage()->getInfo();
        $target = $job->getTargetDimensions();

        if ($target->getWidth() === 0 && $target->getHeight() === 0) {
            $target->setWidth($info->getWidth());
            $target->setHeight($info->getHeight());
        } elseif ($target->getWidth() === 0) {
            $target->setWidth((int)round($target->getHeight() * $info->getWidth() / $info->getHeight()));
        } elseif ($target->getHeight() === 0) {
            $target->setHeight((int)round($target->getWidth() * $info->getHeight() / $info->getWidth()));
        }
    }

    /** Limits the target width/height to the image's height/width if upscale is not allowed. */
    public function checkForUpscale(JobInterface $job): void
    {
        if (!$this->limitUpscaling) {
            return;
        }

        $info = $job->getRequestImage()->getInfo();
        $target = $job->getTargetDimensions();

        if ($target->getWidth() > $info->getWidth()) {
            $target->setWidth($info->getWidth());
        }
        if ($target->getHeight() > $info->getHeight()) {
            $target->setHeight($info->getHeight());
        }
    }
}
