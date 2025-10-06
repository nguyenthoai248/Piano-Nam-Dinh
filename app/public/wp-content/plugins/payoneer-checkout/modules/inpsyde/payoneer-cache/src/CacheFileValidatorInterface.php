<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Cache;

use RangeException;
use RuntimeException;
/**
 * Validates a cache file.
 */
interface CacheFileValidatorInterface
{
    /**
     * Validates a cache file.
     *
     * @param string $path The path to the file.
     *
     * @throws RangeException If file is invalid.
     * @throws RuntimeException If problem validating.
     */
    public function validateCacheFile(string $path) : void;
}
