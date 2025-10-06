<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

/**
 * Describes a context to be used during regular checkout, where no order has yet been placed
 * All data is likely to be produced from WC_Session and WC globals.
 */
class CheckoutContext extends AbstractContext
{
    public function getCart(): \WC_Cart
    {
        return WC()->cart;
    }
    public function getCustomer(): \WC_Customer
    {
        return WC()->customer;
    }
    public function getSession(): \WC_Session
    {
        return WC()->session;
    }
}
