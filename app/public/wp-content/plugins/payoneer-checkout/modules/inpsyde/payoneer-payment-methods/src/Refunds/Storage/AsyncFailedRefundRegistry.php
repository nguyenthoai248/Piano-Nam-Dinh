<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Storage;

/**
 * @see AsyncFailedRefundRegistryTest - unit tests for this class.
 */
class AsyncFailedRefundRegistry implements AsyncFailedRefundRegistryInterface
{
    private const OPTION_KEY = 'payoneer_failed_refund_orders';
    public function addFailedOrder(int $orderId): void
    {
        $failedOrders = $this->failedOrderIds();
        if (in_array($orderId, $failedOrders, \true)) {
            return;
        }
        $failedOrders[] = $orderId;
        $this->updateOption(self::OPTION_KEY, $failedOrders);
    }
    public function removeFailedOrder(int $orderId): void
    {
        $failedOrders = $this->failedOrderIds();
        $filteredOrders = array_filter($failedOrders, static fn($id) => $id !== $orderId);
        if (count($filteredOrders) === count($failedOrders)) {
            return;
        }
        if (!$filteredOrders) {
            $this->clearAllFailedOrders();
            return;
        }
        $this->updateOption(self::OPTION_KEY, array_values($filteredOrders));
    }
    public function hasFailedOrder(int $orderId): bool
    {
        $failedOrders = $this->failedOrderIds();
        return in_array($orderId, $failedOrders, \true);
    }
    public function failedOrderIds(): array
    {
        $optionValue = (array) $this->getOption(self::OPTION_KEY, []);
        $intValues = array_map('intval', $optionValue);
        $positiveValues = array_filter($intValues, static fn($value) => $value > 0);
        return array_values(array_unique($positiveValues));
    }
    public function countFailedOrders(): int
    {
        return count($this->failedOrderIds());
    }
    public function hasFailedOrders(): bool
    {
        return $this->countFailedOrders() > 0;
    }
    public function clearAllFailedOrders(): void
    {
        $this->deleteOption(self::OPTION_KEY);
    }
    /**
     * Allows overriding the get-option mechanic (for testing).
     *
     * phpcs:ignore Inpsyde.CodeQuality.NoAccessors.NoGetter
     * phpcs:ignore Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     * @psalm-suppress MissingReturnType
     * @psalm-suppress MissingParamType
     */
    protected function getOption(string $key, $default = \false)
    {
        return get_option($key, $default);
    }
    /**
     * Allows overriding the update-option mechanic (for testing).
     *
     * phpcs:ignore Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     * @psalm-suppress MissingParamType
     */
    protected function updateOption(string $key, $value): bool
    {
        return update_option($key, $value);
    }
    /**
     * Allows overriding the delete-option mechanic (for testing).
     */
    protected function deleteOption(string $key): bool
    {
        return delete_option($key);
    }
}
