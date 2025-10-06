<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\RefundFinder;

use WC_Order_Refund;
interface RefundFinderInterface
{
    /**
     * Find refund by Payout longId.
     *
     * @param string $payoutId
     *
     * @return WC_Order_Refund|null
     */
    public function findRefundByPayoutLongId(string $payoutId): ?WC_Order_Refund;
}
