<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Response;

use Camelot\ImageAsset\Thumbnail\ThumbnailInterface;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

/**
 * A Thumbnail Response.
 */
final class Response extends HttpFoundationResponse
{
    /** @var ThumbnailInterface */
    private $thumbnail;

    /**
     * Constructor.
     *
     * @param ThumbnailInterface $thumbnail        The thumbnail
     * @param int                $status           The response status code
     * @param array              $headers          An array of response headers
     * @param bool               $public           Thumbnails are public by default
     * @param bool               $autoEtag         Whether the ETag header should be automatically set
     * @param bool               $autoLastModified Whether the Last-Modified header should be automatically set
     */
    public function __construct(
        ThumbnailInterface $thumbnail,
        $status = 200,
        $headers = [],
        $public = true,
        $autoEtag = false,
        $autoLastModified = true
    ) {
        parent::__construct(null, $status, $headers);

        $this->setThumbnail($thumbnail, $autoEtag, $autoLastModified);

        if ($public) {
            $this->setPublic();
        }
    }

    /**
     * Factory method for chainability.
     *
     * @param ThumbnailInterface $thumbnail        The thumbnail
     * @param int                $status           The response status code
     * @param array              $headers          An array of response headers
     * @param bool               $public           Thumbnails are public by default
     * @param bool               $autoEtag         Whether the ETag header should be automatically set
     * @param bool               $autoLastModified Whether the Last-Modified header should be automatically set
     *
     * @return Response
     */
    public static function create(
        $thumbnail = null,
        $status = 200,
        $headers = [],
        $public = true,
        $autoEtag = false,
        $autoLastModified = true
    ) {
        return new static($thumbnail, $status, $headers, $public, $autoEtag, $autoLastModified);
    }

    public function getThumbnail(): ThumbnailInterface
    {
        return $this->thumbnail;
    }

    public function setThumbnail(ThumbnailInterface $thumbnail, bool $autoEtag = false, bool $autoLastModified = true): void
    {
        $this->thumbnail = $thumbnail;
        $this->setContent((string) $thumbnail);

        if ($autoEtag) {
            $this->setAutoEtag();
        }

        if ($autoLastModified) {
            $this->setAutoLastModified();
        }

        $mimeType = $thumbnail->getImage()->getMimeType();
        if ($mimeType) {
            $this->headers->set('Content-Type', $mimeType);
        }
    }

    /** Automatically sets the Last-Modified header according the file modification date. */
    public function setAutoLastModified(): self
    {
        $this->setLastModified($this->thumbnail->getImage()->getMDateTime());

        return $this;
    }

    /** Automatically sets the ETag header according to the checksum of the file. */
    public function setAutoEtag(): self
    {
        $this->setEtag(sha1((string) $this->thumbnail));

        return $this;
    }
}
