<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Cache;

use RuntimeException;
/**
 * Preserves anything that looks like a file extension in the cache key.
 *
 * Uses another resolver for base file name resolution.
 */
class ExtensionPreservingCacheFilePathResolver implements CacheFilePathResolverInterface
{
    /**
     * @var CacheFilePathResolverInterface
     */
    protected $pathResolver;
    public function __construct(CacheFilePathResolverInterface $pathResolver)
    {
        $this->pathResolver = $pathResolver;
    }
    /**
     * @inheritDoc
     */
    public function resolveCacheFilePath(string $key, array $context) : string
    {
        $ext = $this->getPathExtension($key);
        $filePath = $this->pathResolver->resolveCacheFilePath($key, $context);
        if (!empty($ext) && !empty($filePath)) {
            $filePath .= ".{$ext}";
        }
        return $filePath;
    }
    /**
     * Retrieves the extension of the specified path.
     *
     * @param string $path The path to retrieve the extension of.
     *
     * @return string The extension.
     * @throws RuntimeException If problem retrieving.
     */
    protected function getPathExtension(string $path) : string
    {
        $extension = pathinfo($path, \PATHINFO_EXTENSION);
        return $extension;
    }
}
