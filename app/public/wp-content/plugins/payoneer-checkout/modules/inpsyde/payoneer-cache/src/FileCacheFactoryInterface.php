<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Cache;

use RuntimeException;
/**
 * Something that can create a file cache.
 */
interface FileCacheFactoryInterface
{
    /**
     * Creates a file cache that will save files to the specified directory.
     *
     * @param string $baseDir The directory to save cache files to.
     *
     * @return FileCacheInterface The new cache.
     *
     * @throws RuntimeException If problem creating.
     */
    public function createFileCacheFromBaseDir(string $baseDir) : FileCacheInterface;
}
