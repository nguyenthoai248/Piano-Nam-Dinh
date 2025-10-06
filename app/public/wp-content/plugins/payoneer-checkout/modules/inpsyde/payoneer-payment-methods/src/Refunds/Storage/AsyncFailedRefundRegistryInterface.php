<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Storage;

/**
 * Tracks failed async refund attempts for WC_Orders in a global list.
 */
interface AsyncFailedRefundRegistryInterface
{
    /**
     * Records an order when its latest refund attempt has failed.
     * Only failed async refunds should be recorded in this registry.
     */
    public function addFailedOrder(int $orderId): void;
    /**
     * Removes an order from the failed refunds registry.
     * Typically called when a retry succeeds or manual resolution occurs.
     */
    public function removeFailedOrder(int $orderId): void;
    /**
     * Checks if a specific order is currently tracked as having failed refunds.
     */
    public function hasFailedOrder(int $orderId): bool;
    /**
     * All order IDs currently tracked as having failed refunds.
     */
    public function failedOrderIds(): array;
    /**
     * Total number of orders with failed refunds.
     */
    public function countFailedOrders(): int;
    /**
     * Whether any orders have failed refunds requiring attention.
     */
    public function hasFailedOrders(): bool;
    /**
     * Removes all failed refund records.
     * Use when performing bulk resolution or system reset.
     */
    public function clearAllFailedOrders(): void;
}
