<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\OrderWebhookFinder;

use WC_Order;
interface OrderWebhookFinderInterface
{
    /**
     * Find webhook meta in order by Notice Id.
     *
     * @param WC_Order $order
     * @param string $noticeId
     *
     * @return bool
     */
    public function hasRecord(\WC_Order $order, string $noticeId): bool;
}
