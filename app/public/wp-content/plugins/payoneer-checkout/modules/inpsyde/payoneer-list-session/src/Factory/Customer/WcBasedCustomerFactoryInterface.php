<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\Customer;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\FactoryExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerInterface;
use WC_Customer;
/**
 * Service able to convert an instance of WC_Customer into
 */
interface WcBasedCustomerFactoryInterface
{
    /**
     * Convert a WC_Customer into Payoneer SDK Customer.
     *
     * @param WC_Customer $wcCustomer WC customer to get data from.
     *
     * @return CustomerInterface Created Customer instance.
     *
     * @throws FactoryExceptionInterface If failed to transform.
     */
    public function createCustomerFromWcCustomer(WC_Customer $wcCustomer): CustomerInterface;
}
