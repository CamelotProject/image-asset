<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Exception;

use Camelot\ImageAsset\Filesystem\ImageInterface;
use RuntimeException;

/**
 * @codeCoverageIgnore
 */
class NoFallbackException extends RuntimeException implements ExceptionInterface
{
    /** @var ImageInterface */
    private $image;

    public function __construct(ImageInterface $image, NotFoundHttpException $previous, int $code = 1)
    {
        $message = sprintf(
            'There was an error with the thumbnail image requested' . PHP_EOL .
            '    Relative path: %s' . PHP_EOL .
            '    Real path: %s' . PHP_EOL .
            '',
            $image->getRelativePath(),
            $image->getPath(),
        );
        $this->image = $image;

        parent::__construct($message, $code, $previous);
    }

    public function getImage(): ImageInterface
    {
        return $this->image;
    }
}
