<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use Syde\Vendor\Psr\Http\Message\UriInterface;
use RuntimeException;
/**
 * Something that can resolve a path to a URL.
 */
interface UrlResolverInterface
{
    /**
     * Resolves a path to a URL.
     *
     * @param string $path The path to resolves.
     *
     * @return UriInterface The resolved URL.
     * @throws RuntimeException If problem resolving.
     */
    public function resolveUrl(string $path) : UriInterface;
}
