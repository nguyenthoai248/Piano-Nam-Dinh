<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\StateProvider;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutExceptionInterface;
use OutOfBoundsException;
use WC_Countries;
class StateProvider implements StateProviderInterface
{
    /**
     * @var WC_Countries
     */
    protected $countries;
    public function __construct(WC_Countries $countries)
    {
        $this->countries = $countries;
    }
    /**
     * @inheritDoc
     */
    public function provideStateNameByCountryAndStateCode(string $countryCode, string $stateCode): string
    {
        $states = $this->countries->get_states();
        if (!isset($states[$countryCode][$stateCode])) {
            $message = sprintf('Cannot find state %1$s for country %2$s', $stateCode, $countryCode);
            throw new class($message) extends OutOfBoundsException implements CheckoutExceptionInterface
            {
            };
        }
        return (string) $states[$countryCode][$stateCode];
    }
}
