<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Event;

use Camelot\ImageAsset\Transaction\JobInterface;

final class CreatorEvent
{
    public const THUMBNAIL_CREATE = 'thumbnail.create';

    /** @var JobInterface */
    private $job;
    /** @var string */
    private $data;

    public function __construct(JobInterface $job, string $data)
    {
        $this->job = clone $job;
        $this->data = $data;
    }

    public function getJob(): JobInterface
    {
        return $this->job;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $thumbnailData): self
    {
        $this->data = $thumbnailData;

        return $this;
    }
}
