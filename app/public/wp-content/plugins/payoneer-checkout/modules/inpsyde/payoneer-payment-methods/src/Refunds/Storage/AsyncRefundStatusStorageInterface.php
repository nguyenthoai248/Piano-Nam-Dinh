<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Storage;

use WC_Order;
/**
 * Manages refund status tracking for async refunds on order objects.
 * Enables status persistence across multiple HTTP requests until webhook completion.
 */
interface AsyncRefundStatusStorageInterface
{
    /**
     * @return string|null Returns null when no async refund status is set
     */
    public function currentRefundStatus(WC_Order $wcOrder): ?string;
    /**
     * Updates the refund status without validation.
     * Business logic validation should happen at service layer.
     */
    public function changeRefundStatusTo(WC_Order $wcOrder, ?string $status): void;
    /**
     * Removes stored status, e.g. when dismissing a failed refund notification.
     */
    public function clearRefundStatus(WC_Order $wcOrder): void;
}
