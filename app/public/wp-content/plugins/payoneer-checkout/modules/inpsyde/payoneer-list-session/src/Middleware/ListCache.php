<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Middleware;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
class ListCache implements ListCacheInterface
{
    /**
     * @var array<string,ListInterface>
     */
    private array $cache = [];
    public function cacheList(ListInterface $list): string
    {
        // Retrieve the long ID from the list's identification.
        $longId = $list->getIdentification()->getLongId();
        // Store the list in the cache using its long ID as the key.
        $this->cache[$longId] = $list;
        // Return the unique long ID of the cached list.
        return $longId;
    }
    public function hasCachedLongId(string $longId): bool
    {
        // Return whether the specified long ID exists in the cache.
        return isset($this->cache[$longId]);
    }
    public function getCachedListByLongId(string $longId): ListInterface
    {
        if (!$this->hasCachedLongId($longId)) {
            throw new \OutOfBoundsException('Requested List not found in cache.');
        }
        return $this->cache[$longId];
    }
    public function clear(): void
    {
        $this->cache = [];
    }
}
