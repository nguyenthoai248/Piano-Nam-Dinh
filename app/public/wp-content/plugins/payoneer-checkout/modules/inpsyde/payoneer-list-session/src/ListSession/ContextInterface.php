<?php

namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

interface ContextInterface extends \ArrayAccess
{
    public function getCart(): ?\WC_Cart;
    public function getCustomer(): ?\WC_Customer;
    public function getSession(): ?\WC_Session;
    public function getOrder(): ?\WC_Order;
}
