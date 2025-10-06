<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use RuntimeException;
/**
 * Something that can resolve a source path to an actual path,
 */
interface PathResolverInterface
{
    /**
     * @param string $sourcePath A path to resolve.
     *
     * @return string Resolved path.
     * @throws RuntimeException If problem resolving.
     */
    public function resolvePath(string $sourcePath) : string;
}
