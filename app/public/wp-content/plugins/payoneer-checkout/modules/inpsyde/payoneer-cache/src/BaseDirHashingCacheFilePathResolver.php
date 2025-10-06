<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Cache;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\HasherInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\StringStreamFactoryInterface;
use Syde\Vendor\Psr\Http\Message\StreamInterface;
use RuntimeException;
/**
 * Can resolve a path to a cache file path from cache parameters.
 */
class BaseDirHashingCacheFilePathResolver implements CacheFilePathResolverInterface
{
    /**
     * @var string
     */
    protected $cacheDir;
    /**
     * @var HasherInterface
     */
    protected $hasher;
    /**
     * @var string
     */
    protected $segmentSeparator;
    /**
     * @var StringStreamFactoryInterface
     */
    protected $stringStreamFactory;
    public function __construct(string $cacheDir, HasherInterface $hasher, string $segmentSeparator, StringStreamFactoryInterface $stringStreamFactory)
    {
        $this->cacheDir = $cacheDir;
        $this->hasher = $hasher;
        $this->segmentSeparator = $segmentSeparator;
        $this->stringStreamFactory = $stringStreamFactory;
    }
    /**
     * @inheritDoc
     */
    public function resolveCacheFilePath(string $key, array $context) : string
    {
        $baseDir = $this->cacheDir;
        $segSeparator = $this->segmentSeparator;
        $keyHash = $this->hasher->getHash($this->createStreamFromString($key));
        $contextHash = $this->getMapHash($context);
        $fileName = $this->getHash($this->createStreamFromString("{$keyHash}{$segSeparator}{$contextHash}"));
        $filePath = "{$baseDir}/{$fileName}";
        return $filePath;
    }
    /**
     * Retrieves a hash of the given map.
     *
     * @param array<string, mixed> $map The map to hash.
     *
     * @return string The hash.
     * @throws RuntimeException If problem retrieving.
     */
    protected function getMapHash(array $map) : string
    {
        $map = $this->sortArrayByKey($map, \true);
        $serializedContext = serialize($map);
        $hash = $this->getHash($this->createStreamFromString($serializedContext));
        return $hash;
    }
    /**
     * Creates a stream that exposes a string.
     *
     * @param string $string The string.
     *
     * @return StreamInterface The stream.
     * @throws RuntimeException If problem creating.
     */
    protected function createStreamFromString(string $string) : StreamInterface
    {
        return $this->stringStreamFactory->createStreamFromString($string);
    }
    /**
     * Retrieves a hash of the given stream.
     *
     * @param StreamInterface $string The stream to retrieve the hash of.
     *
     * @return string The hash.
     * @throws RuntimeException If problem hashing.
     */
    protected function getHash(StreamInterface $string) : string
    {
        return $this->hasher->getHash($string);
    }
    /**
     * Retrieves a copy of the input array, sorted by key, in the specified order.
     *
     * @param array $array The array to sort.
     * @param bool $isAscending If true, the sort order is ascending; otherwise, descending.
     *
     * @return array The input array, sorted.
     * @throws RuntimeException If problem retrieving.
     */
    protected function sortArrayByKey(array $array, bool $isAscending) : array
    {
        ksort($array);
        if (!$isAscending) {
            $array = array_reverse($array);
        }
        return $array;
    }
}
