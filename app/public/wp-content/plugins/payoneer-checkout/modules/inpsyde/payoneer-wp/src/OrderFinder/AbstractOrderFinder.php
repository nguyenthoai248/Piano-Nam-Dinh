<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\OrderFinder;

use WC_Order;
abstract class AbstractOrderFinder implements OrderFinderInterface
{
    protected string $transactionIdOrderFieldName;
    public function __construct(string $transactionIdOrderFieldName)
    {
        $this->transactionIdOrderFieldName = $transactionIdOrderFieldName;
    }
    public function findOrdersByTransactionId(string $transactionId, int $limit): array
    {
        $args = $this->args($transactionId);
        $args['limit'] = $limit;
        /** @var WC_Order[] $orders */
        $orders = wc_get_orders($args);
        /** This may look like an extra check as we requested order by this meta field.
         * But WooCommerce returns just latest orders when it doesn't understand request,
         * and we had bugs because of that. So this is a safety net.
         */
        return array_filter($orders, fn(WC_Order $order) => $order->get_meta($this->transactionIdOrderFieldName) === $transactionId);
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
