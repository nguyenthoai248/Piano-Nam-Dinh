<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\OrderFinder;

use WC_Order;
abstract class AbstractOrderFinder implements OrderFinderInterface
{
    protected string $transactionIdOrderFieldName;
    public function __construct(string $transactionIdOrderFieldName)
    {
        $this->transactionIdOrderFieldName = $transactionIdOrderFieldName;
    }
    public function findOrderByTransactionId(string $transactionId): ?WC_Order
    {
        /** @var WC_Order[] $orders */
        $orders = wc_get_orders($this->args($transactionId));
        if ($orders && $orders[0]->get_meta($this->transactionIdOrderFieldName) === $transactionId) {
            return $orders[0];
        }
        return null;
    }
    /**
     * Get arguments for get_orders function to find an order by given transaction ID.
     *
     * @param string $transactionId Payoneer transaction id that must be included in args.
     *
     * @return array Arguments suitable for get_orders() function.
     */
    abstract protected function args(string $transactionId): array;
}
