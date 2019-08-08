<?php

declare(strict_types=1);

namespace Camelot\ImageAsset\Filesystem;

use IteratorAggregate;

interface FinderInterface extends IteratorAggregate
{
    /**
     * Searches files and directories which match defined rules.
     *
     * @param string[] $dirs A directory path or an array of directories
     */
    public function in(array $dirs): self;

    /**
     * Adds rules that files must match.
     *
     * You can use patterns (delimited with / sign), globs or simple strings.
     *
     *     $finder->name(['*.php'])
     *     $finder->name(['/\.php$/']) // same as above
     *     $finder->name(['test.php'])
     *     $finder->name(['test.py', 'test.php'])
     *
     * @param string[] $patterns A pattern (a regexp, a glob, or a string) or an array of patterns
     */
    public function name(array $patterns): self;

    /**
     * Adds rules that files must not match.
     *
     * @param string[] $patterns A pattern (a regexp, a glob, or a string) or an array of patterns
     */
    public function notName(array $patterns): self;

    /** Counts all the results collected by the iterators. */
    public function count(): int;

    /**
     * Excludes directories.
     *
     * Directories passed as argument must be relative to the ones defined with the `in()` method. For example:
     *
     *     $finder->in(__DIR__)->exclude(['ruby']);
     *
     * @param array $dirs A directory path or an array of directories
     */
    public function exclude(array $dirs): self;

    /**
     * Adds tests for the directory depth.
     *
     * Usage:
     *
     *     $finder->depth(['> 1']) // the Finder will start matching at level 1.
     *     $finder->depth(['< 3']) // the Finder will descend at most 3 levels of directories below the starting point.
     *     $finder->depth(['>= 1', '< 3'])
     */
    public function depth(array $levels): self;

    /** Check if the any results were found. */
    public function hasResults(): bool;
}
