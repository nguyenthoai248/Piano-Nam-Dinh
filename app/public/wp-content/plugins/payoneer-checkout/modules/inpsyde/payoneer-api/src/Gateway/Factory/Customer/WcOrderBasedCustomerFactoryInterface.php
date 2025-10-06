<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\Factory\Customer;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerInterface;
use WC_Order;
interface WcOrderBasedCustomerFactoryInterface
{
    /**
     * Create a new Customer using data from provided order.
     *
     * @param WC_Order $order To get data from
     *
     * @return CustomerInterface Created customer
     */
    public function createCustomer(WC_Order $order): CustomerInterface;
}
