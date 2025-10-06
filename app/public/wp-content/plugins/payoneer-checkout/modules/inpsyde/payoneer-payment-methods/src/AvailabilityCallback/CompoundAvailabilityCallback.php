<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\AvailabilityCallback;

use WC_Payment_Gateway;
class CompoundAvailabilityCallback implements AvailabilityCallbackInterface
{
    /**
     * @var AvailabilityCallbackInterface[]
     */
    protected array $callbacks;
    public function __construct(AvailabilityCallbackInterface ...$callbacks)
    {
        $this->callbacks = $callbacks;
    }
    public function __invoke(WC_Payment_Gateway $gateway): bool
    {
        foreach ($this->callbacks as $callback) {
            if (!$callback($gateway)) {
                return \false;
            }
        }
        return \true;
    }
}
