<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Cache;

use RuntimeException;
use Stringable;
/**
 * Resolves cache parameters to a file path.
 *
 * @psalm-type MapOfScalars = array<string, Stringable|scalar|array<string, Stringable|scalar>>
 * @psalm-type Options = array<string, Stringable|scalar|MapOfScalars>
 * @psalm-type Context = array<string, Stringable|scalar|Options>
 */
interface CacheFilePathResolverInterface
{
    /**
     * Resolves cache parameters to a file path.
     *
     * @param string $key The cache key.
     * @param array<string, mixed> $context The cache context.
     * @psalm-param Context $context
     *
     * @return string The path to a cache file.
     *
     * @throws RuntimeException If problem resolving.
     */
    public function resolveCacheFilePath(string $key, array $context) : string;
}
