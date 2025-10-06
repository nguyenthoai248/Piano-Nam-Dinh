<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\ListSession;

use Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutException;

class WcOrderListSessionRemover implements OrderAwareListSessionRemover
{
    protected $key;
    protected $order;

    public function __construct(
        string $key,
        \WC_Order $order = null
    ) {

        $this->key = $key;
        $this->order = $order;
    }

    public function clear(): void
    {
        $order = $this->ensureOrder();
        $order->delete_meta_data($this->key);
        $order->save();
    }

    /**
     * @throws CheckoutException
     */
    private function ensureOrder(): \WC_Order
    {
        if (! $this->order) {
            throw new CheckoutException('No WC_Order configured');
        }

        return $this->order;
    }

    /**
     * @inheritDoc
     *
     * @param \WC_Order $order
     *
     * @return WcOrderListSessionRemover&static
     */
    public function withOrder(\WC_Order $order): OrderAwareObject
    {
        $new = clone $this;
        $new->order = $order;

        return $new;
    }
}
