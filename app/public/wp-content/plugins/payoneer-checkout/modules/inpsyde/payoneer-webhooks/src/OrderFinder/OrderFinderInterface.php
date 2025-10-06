<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\OrderFinder;

use WC_Order;
/**
 * A service able to find WC order by Payoneer transaction ID.
 */
interface OrderFinderInterface
{
    /**
     * Find order with given Payoneer transaction id.
     *
     * @param string $transactionId Transaction ID to find order by it.
     *
     * @return WC_Order|null Found WC order.
     *
     */
    public function findOrderByTransactionId(string $transactionId): ?WC_Order;
}
