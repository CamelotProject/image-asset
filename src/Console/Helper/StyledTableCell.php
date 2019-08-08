<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Console\Helper;

use Symfony\Component\Console\Helper\TableCell;

final class StyledTableCell extends TableCell
{
    public static function createSuccess(string $value = '', array $options = [])
    {
        return new self("<fg=black;bg=green>{$value}</>", $options);
    }

    public static function createWarning(string $value = '', array $options = [])
    {
        return new self("<fg=black;bg=yellow>{$value}</>", $options);
    }

    public static function createError(string $value = '', array $options = [])
    {
        return new self("<fg=white;bg=red>{$value}</>", $options);
    }
}
