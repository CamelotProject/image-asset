<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Tests\Unit\Console\Helper;

use Camelot\ImageAsset\Console\Helper\StyledTableCell;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\ImageAsset\Console\Helper\StyledTableCell
 */
final class StyledTableCellTest extends TestCase
{
    public function testCreateSuccess(): void
    {
        $cell = StyledTableCell::createSuccess('message');
        static::assertInstanceOf(StyledTableCell::class, $cell);
        static::assertSame('<fg=black;bg=green>message</>', (string) $cell);
    }

    public function testCreateWarning(): void
    {
        $cell = StyledTableCell::createWarning('message');
        static::assertInstanceOf(StyledTableCell::class, $cell);
        static::assertSame('<fg=black;bg=yellow>message</>', (string) $cell);
    }

    public function testCreateError(): void
    {
        $cell = StyledTableCell::createError('message');
        static::assertInstanceOf(StyledTableCell::class, $cell);
        static::assertSame('<fg=white;bg=red>message</>', (string) $cell);
    }
}
