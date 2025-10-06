<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Cache;

use RangeException;
/**
 * Can validate a cache file.
 */
class FileExistsFileValidator implements CacheFileValidatorInterface
{
    /**
     * @inheritDoc
     */
    public function validateCacheFile(string $path) : void
    {
        if (!file_exists($path)) {
            throw new RangeException(sprintf('File at path "%1$s" is invalid', $path));
        }
    }
}
