<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use RuntimeException;
/**
 * Misc generic path-related functionality that does not require much abstraction.
 */
trait PathHelperTrait
{
    /**
     * Normalizes a path.
     *
     * Operates only on the path string .Does not access the filesystem.
     *
     * @param string $basePath The path string.
     *
     * @return string The normalized path.
     * @throws RuntimeException If problem normalizing.
     */
    protected function normalizePath(string $basePath) : string
    {
        return rtrim(ltrim($basePath), " \t\n\r\x00\v/");
    }
}
