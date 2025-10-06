<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\AvailabilityCallback;

use WC_Payment_Gateway;
/**
 * Only calls the decorated callback if a predicate returns true
 */
class ConditionalCallbackDecorator implements AvailabilityCallbackInterface
{
    protected AvailabilityCallbackInterface $inner;
    protected bool $default;
    /**
     * @var callable
     */
    private $predicate;
    public function __construct(callable $predicate, AvailabilityCallbackInterface $inner, bool $default = \true)
    {
        $this->predicate = $predicate;
        $this->inner = $inner;
        $this->default = $default;
    }
    public function __invoke(WC_Payment_Gateway $gateway): bool
    {
        if (($this->predicate)()) {
            return ($this->inner)($gateway);
        }
        return $this->default;
    }
}
