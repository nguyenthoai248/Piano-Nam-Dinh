<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Middleware;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
interface ListCacheInterface
{
    /**
     * Caches a list using its long ID.
     *
     * @param ListInterface $list The list to be cached.
     *     method).
     *
     * @return string The unique long ID of the cached list.
     */
    public function cacheList(ListInterface $list): string;
    /**
     * Checks if a list is already cached by its long ID.
     *
     * @param string $longId The long ID to check in the cache.
     *
     * @return bool Returns true if the list is cached, false otherwise.
     */
    public function hasCachedLongId(string $longId): bool;
    /**
     * Retrieves a List from cache by given longId.
     *
     * @param string $longId The longId of the List to get.
     *
     * @return ListInterface List from cache with given longId.
     *
     * @throws \OutOfBoundsException If cached List with provided LongId wasn't found.
     */
    public function getCachedListByLongId(string $longId): ListInterface;
    /**
     * Removes all stored Lists.
     *
     * @return void
     */
    public function clear(): void;
}
