<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Storage;

use WC_Order_Refund;
/**
 * @see PayoutToRefundMappingTest - unit tests for this class.
 */
class PayoutToRefundMapping implements PayoutToRefundMappingInterface
{
    protected string $payoutIdFieldName;
    public function __construct(string $payoutIdFieldName)
    {
        $this->payoutIdFieldName = $payoutIdFieldName;
    }
    public function storePayoutId(WC_Order_Refund $wcRefund, string $payoutId): void
    {
        do_action('payoneer-checkout.refund.map_refund_to_payout', ['refundId' => $wcRefund->get_id(), 'payoutId' => $payoutId]);
        $wcRefund->update_meta_data($this->payoutIdFieldName, $payoutId);
        $wcRefund->save_meta_data();
    }
    public function payoutId(WC_Order_Refund $wcRefund): ?string
    {
        $value = $wcRefund->get_meta($this->payoutIdFieldName);
        return $value ? (string) $value : null;
    }
    public function hasPayoutId(WC_Order_Refund $wcRefund): bool
    {
        $storedId = (string) $this->payoutId($wcRefund);
        return $storedId !== '';
    }
}
