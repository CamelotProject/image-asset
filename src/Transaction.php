<?php

namespace Bolt\Thumbs;

use Bolt\Filesystem\Image;

/**
 * A storage entity for a thumbnail creation request.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class Transaction
{
    /** @var Image */
    protected $srcImage;
    /** @var Image */
    protected $errorImage;

    /** @var string */
    protected $action;
    /** @var Dimensions */
    protected $target;

    /** @var bool */
    protected $allowUpscale;
    /** @var Color */
    protected $background;

    /**
     * Transaction Constructor.
     */
    public function __construct()
    {
        $this->action = 'crop';
        $this->target = new Dimensions();
        $this->allowUpscale = false;
        $this->background = Color::white();
    }

    /**
     * Chainable Constructor.
     *
     * @return Transaction
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Returns a hash string of this transaction.
     *
     * @return string
     */
    public function getHash()
    {
        $path = str_replace('/', '_', $this->srcImage->getPath());
        return join('-', [$path, $this->action, $this->target->getWidth(), $this->target->getHeight()]);
    }

    /**
     * @return Image
     */
    public function getSrcImage()
    {
        return $this->srcImage;
    }

    /**
     * @param Image $srcImage
     *
     * @return Transaction
     */
    public function setSrcImage(Image $srcImage)
    {
        $this->srcImage = $srcImage;

        return $this;
    }

    /**
     * @return Image
     */
    public function getErrorImage()
    {
        return $this->errorImage;
    }

    /**
     * @param Image $errorImage
     *
     * @return Transaction
     */
    public function setErrorImage(Image $errorImage)
    {
        $this->errorImage = $errorImage;

        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     *
     * @return Transaction
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return Dimensions
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param Dimensions $target
     *
     * @return Transaction
     */
    public function setTarget(Dimensions $target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getAllowUpscale()
    {
        return $this->allowUpscale;
    }

    /**
     * @param boolean $allowUpscale
     *
     * @return Transaction
     */
    public function setAllowUpscale($allowUpscale)
    {
        $this->allowUpscale = (bool) $allowUpscale;

        return $this;
    }

    /**
     * @return Color
     */
    public function getBackground()
    {
        return $this->background;
    }

    /**
     * @param Color $background
     *
     * @return Transaction
     */
    public function setBackground(Color $background)
    {
        $this->background = $background;

        return $this;
    }
}
