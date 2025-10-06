<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\StateProvider;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutException;
use OutOfBoundsException;
/**
 * A service providing state name by country and state code.
 */
interface StateProviderInterface
{
    /**
     * @return string State name.
     *
     * @throws OutOfBoundsException|CheckoutException If state not found.
     */
    public function provideStateNameByCountryAndStateCode(string $countryCode, string $stateCode): string;
}
