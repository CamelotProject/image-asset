<?php

declare(strict_types=1);

namespace Camelot\ImageAssets\Response;

use Camelot\ImageAssets\Image\Thumbnail;
use Camelot\ImageAssets\Transaction;

/**
 * @author Carson Full <carsonfull@gmail.com>
 */
interface ResponderInterface
{
    /**
     * Process the transaction and return a thumbnail.
     *
     *
     * @return Thumbnail
     */
    public function respond(Transaction $transaction);
}
