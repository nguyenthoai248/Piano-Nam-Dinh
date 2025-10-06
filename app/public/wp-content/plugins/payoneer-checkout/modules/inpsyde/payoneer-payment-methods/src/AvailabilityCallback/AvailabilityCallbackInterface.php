<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\AvailabilityCallback;

use WC_Payment_Gateway;
interface AvailabilityCallbackInterface
{
    public function __invoke(WC_Payment_Gateway $gateway): bool;
}
