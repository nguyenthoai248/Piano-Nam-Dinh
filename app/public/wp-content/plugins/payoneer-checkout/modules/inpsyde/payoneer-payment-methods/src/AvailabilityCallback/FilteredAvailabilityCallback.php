<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\AvailabilityCallback;

use WC_Payment_Gateway;
class FilteredAvailabilityCallback implements AvailabilityCallbackInterface
{
    protected AvailabilityCallbackInterface $callback;
    public function __construct(AvailabilityCallbackInterface $callback)
    {
        $this->callback = $callback;
    }
    public function __invoke(WC_Payment_Gateway $gateway): bool
    {
        return (bool) apply_filters('payoneer-checkout.payment_gateway_is_available', $this->callback->__invoke($gateway));
    }
}
