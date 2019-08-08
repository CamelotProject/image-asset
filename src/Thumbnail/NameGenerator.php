<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Thumbnail;

use Camelot\ImageAsset\Transaction\JobInterface;
use const DIRECTORY_SEPARATOR;

final class NameGenerator
{
    /** @var string */
    private $pattern;

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    public function generate(int $width, int $height, string $action, string $filePath): string
    {
        $raw = preg_replace(['#\{width\}#', '#\{height\}#', '#\{action\}#', '#\{file\}#'], [$width, $height, $action, $filePath], $this->pattern);

        return implode(DIRECTORY_SEPARATOR, array_filter(explode('/', $raw)));
    }

    public function generateFromJob(JobInterface $job): string
    {
        $dim = $job->getTargetDimensions();

        return $this->generate($dim->getWidth(), $dim->getHeight(), (string) $job->getAction(), $job->getRequestPath());
    }
}
