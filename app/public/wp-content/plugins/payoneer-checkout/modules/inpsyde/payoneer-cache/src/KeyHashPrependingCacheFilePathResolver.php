<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Cache;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\HasherInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\StringStreamFactoryInterface;
use Syde\Vendor\Psr\Http\Message\StreamInterface;
use RuntimeException;
/**
 * Prepends a hash of the key to a base cache file path.
 *
 * Uses another resolver to determine the base file path.
 * This is useful to put a unifying component into all keys of cache entries that share a cache key.
 */
class KeyHashPrependingCacheFilePathResolver implements CacheFilePathResolverInterface
{
    /**
     * @var CacheFilePathResolverInterface
     */
    protected $pathResolver;
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
    public function __construct(CacheFilePathResolverInterface $pathResolver, HasherInterface $hasher, string $segmentSeparator, StringStreamFactoryInterface $stringStreamFactory)
    {
        $this->pathResolver = $pathResolver;
        $this->hasher = $hasher;
        $this->segmentSeparator = $segmentSeparator;
        $this->stringStreamFactory = $stringStreamFactory;
    }
    /**
     * @inheritDoc
     */
    public function resolveCacheFilePath(string $key, array $context) : string
    {
        $separator = $this->segmentSeparator;
        $filePath = $this->pathResolver->resolveCacheFilePath($key, $context);
        $keyHash = $this->hasher->getHash($this->createStreamFromString($key));
        $reattach = '';
        $slash = $this->stringEndsWith($filePath, '/');
        if ($slash) {
            $reattach = "{$slash}{$reattach}";
            $filePath = $this->getSubstring($filePath, -strlen($slash), strlen($slash));
        }
        $basename = basename($filePath);
        if ($basename) {
            $reattach = "{$basename}{$reattach}";
            $filePath = $this->getSubstring($filePath, 0, -strlen($basename));
        }
        $filePath .= $keyHash;
        if (!empty($reattach)) {
            $filePath .= "{$separator}{$reattach}";
        }
        return $filePath;
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
     * Retrieves the end of a string if it matches the specified end.
     *
     * @param string $string The string that may end with another string.
     * @param string $possibleEnd The possible end of the string.
     *
     * @return string The end, if it matched; otherwise, empty string.
     *
     * @throws RuntimeException If problem determining.
     */
    protected function stringEndsWith(string $string, string $possibleEnd) : string
    {
        $endLength = strlen($possibleEnd);
        $actualEnd = substr($string, -$endLength, $endLength);
        return $actualEnd === $possibleEnd ? $actualEnd : '';
    }
    /**
     * Retrieves a part of the specified string.
     *
     * @see substr()
     *
     * @param string $string The string to retrieve a part of.
     * @param int $offset The zero-based position to start the part from.
     * @param int|null $length The length of the part, if not null;
     *                         otherwise, remainder of the string.
     *
     * @return string The part of the string.
     *
     * @throws RuntimeException If problem retrieving.
     */
    protected function getSubstring(string $string, int $offset, ?int $length) : string
    {
        $result = substr($string, $offset, $length);
        if ($result === \false) {
            throw new RuntimeException(sprintf('Could not extract substring of "%1$s" with offset "%2$d" and length "%3$s"', $string, $offset, $length ?? 'null'));
        }
        return $result;
    }
}
