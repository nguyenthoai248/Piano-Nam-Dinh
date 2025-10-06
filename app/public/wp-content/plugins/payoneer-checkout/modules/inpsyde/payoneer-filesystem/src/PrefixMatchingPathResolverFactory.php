<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

/**
 * Creates prefix-matching path resolvers.
 */
class PrefixMatchingPathResolverFactory implements PrefixMatchingPathResolverFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createPathResolverFromMappings(array $mappings, string $baseDir) : PathResolverInterface
    {
        return new PrefixMatchingPathResolver($mappings, $baseDir);
    }
}
