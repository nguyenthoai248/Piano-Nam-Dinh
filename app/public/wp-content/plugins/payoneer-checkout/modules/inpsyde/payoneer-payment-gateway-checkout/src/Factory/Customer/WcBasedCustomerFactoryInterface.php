<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\Factory\Customer;

use Inpsyde\PayoneerForWoocommerce\Checkout\Factory\FactoryExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerInterface;
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
