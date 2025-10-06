<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use RuntimeException;
/**
 * Something that can create path resolvers from path mappings.
 */
interface PrefixMatchingPathResolverFactoryInterface
{
    /**
     * Creates a path resolver.
     *
     * It will resolve paths by substituting prefixes in paths according to matches
     * from the specified map, and will add a base directory path.
     *
     * @param array<string, string> $mappings The map of source paths to destination paths.
     * @param string $baseDir The base directory.
     *
     * @return PathResolverInterface The resolver
     *
     * @throws RuntimeException If problem creating.
     */
    public function createPathResolverFromMappings(array $mappings, string $baseDir) : PathResolverInterface;
}
