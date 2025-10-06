<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\OrderFinder;

/**
 * A service able to find WC order by Payoneer transaction ID.
 */
interface OrderFinderInterface
{
    /**
     * Find orders with given Payoneer transaction id.
     *
     * @param string $transactionId
     * @param int $limit
     *
     * @return \WC_Order[]
     */
    public function findOrdersByTransactionId(string $transactionId, int $limit): array;
}
