<?php

namespace Inpsyde\PayoneerForWoocommerce\Checkout\Factory\ListSession;

use Inpsyde\PayoneerForWoocommerce\Checkout\Factory\FactoryExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use WC_Cart;
use WC_Customer;

interface WcBasedListSessionFactoryInterface
{
    /**
     * @throws FactoryExceptionInterface
     */
    public function createList(
        WC_Customer $customer,
        WC_Cart $cart,
        string $listSecurityToken
    ): ListInterface;
}
