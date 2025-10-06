<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Cache;

use RuntimeException;
/**
 * Something that can create a cache file path resolver
 * which will resolve cache files to a base directory.
 */
interface BaseDirFilePathResolverFactoryInterface
{
    /**
     * Creates a cache file path resolver that will resolve paths to the specified base directory.
     *
     * @param string $baseDir The base directory to resolve file paths to.
     *
     * @return CacheFilePathResolverInterface The new resolver.
     *
     * @throws RuntimeException If problem creating.
     */
    public function createFilePathResolverFromBaseDir(string $baseDir) : CacheFilePathResolverInterface;
}
