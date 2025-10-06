<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\RefundFinder;

use WC_Order_Refund;
class RefundFinder implements RefundFinderInterface
{
    /**
     * @var string
     */
    protected $payoutIdFieldName;
    /**
     * @param string $payoutIdFieldName
     */
    public function __construct(string $payoutIdFieldName)
    {
        $this->payoutIdFieldName = $payoutIdFieldName;
    }
    /**
     * @inheritDoc
     */
    public function findRefundByPayoutLongId(string $payoutId): ?WC_Order_Refund
    {
        /** @var WC_Order_Refund[] $found */
        $found = wc_get_orders(['type' => 'shop_order_refund', 'limit' => 1, $this->payoutIdFieldName => $payoutId]);
        if (empty($found[0])) {
            return null;
        }
        $order = $found[0];
        $payoutOrderId = $order->get_meta($this->payoutIdFieldName);
        if ($payoutOrderId !== $payoutId) {
            return null;
        }
        return $order;
    }
}
