<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes;

class TaxConfigurationChecker
{
    public function taxCanBeApplied() : bool
    {
        $currentPaymentMethod = WC()->session->get('chosen_payment_method');
        $isPayoneerPaymentMethod = $currentPaymentMethod === 'payoneer-checkout';
        $pricesAreExclusiveOfTax = get_option('woocommerce_prices_include_tax') === 'no';
        return $pricesAreExclusiveOfTax && $isPayoneerPaymentMethod;
    }
}
