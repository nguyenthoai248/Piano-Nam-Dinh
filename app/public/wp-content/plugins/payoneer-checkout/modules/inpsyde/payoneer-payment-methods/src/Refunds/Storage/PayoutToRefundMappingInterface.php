<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Storage;

use WC_Order_Refund;
/**
 * Correlates Payoneer payout IDs with WooCommerce refunds.
 * Enables webhook processing to locate the correct refund object.
 */
interface PayoutToRefundMappingInterface
{
    /**
     * Assigns a payout longId to the refund, making it detectable by
     * the RefundFinderInterface.
     */
    public function storePayoutId(WC_Order_Refund $wcRefund, string $payoutId): void;
    /**
     * Returns the longId assigned to the given refund.
     */
    public function payoutId(WC_Order_Refund $wcRefund): ?string;
    /**
     * Checks, whether the provided refund is already mapped to a payout.
     */
    public function hasPayoutId(WC_Order_Refund $wcRefund): bool;
}
