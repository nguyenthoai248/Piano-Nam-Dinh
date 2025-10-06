<?php

namespace Inpsyde\PayoneerForWoocommerce\Checkout\ListSession;

interface OrderAwareObject
{
    /**
     * @param \WC_Order $order
     *
     * @return static
     */
    public function withOrder(\WC_Order $order): self;
}
