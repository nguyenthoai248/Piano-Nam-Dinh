<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Storage;

use WC_Order;
use WC_Order_Refund;
/**
 * Preserves refund intention data for async refund processing.
 *
 * Critical for maintaining line item details and user context that
 * webhooks cannot provide.
 */
interface AsyncRefundIntentStorageInterface
{
    /**
     * Preserves refund details initiated from WC admin for later webhook processing.
     *
     * Captures line items, amounts, and user context before API call is made.
     */
    public function storeRefundIntent(WC_Order $wcOrder, WC_Order_Refund $wcRefund): void;
    /**
     * Reconstructs the original refund intention for webhook processing.
     *
     * Returns null when no intention data exists for the order.
     */
    public function refundIntent(WC_Order $wcOrder): ?WC_Order_Refund;
    /**
     * Removes stored intention data after successful refund completion.
     */
    public function clearRefundIntent(WC_Order $wcOrder): void;
    /**
     * Prevents duplicate async refund processing.
     */
    public function hasRefundIntent(WC_Order $wcOrder): bool;
}
