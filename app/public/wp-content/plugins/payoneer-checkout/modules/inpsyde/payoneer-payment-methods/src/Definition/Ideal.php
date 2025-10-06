<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Definition;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\ListCondition\MatchNetworkCodeCondition;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\ListCondition\MatchNetworkGroupingCondition;
class Ideal extends AbstractPayoneerPaymentMethodDefinition
{
    public function id(): string
    {
        return 'payoneer-ideal';
    }
    public function availabilityListConditions(): iterable
    {
        yield new MatchNetworkGroupingCondition('DIRECT_DEBIT');
        yield new MatchNetworkCodeCondition('IDEAL');
    }
    public function dropIn(): string
    {
        return 'ideal';
    }
    public function fallbackTitle(): string
    {
        return __('iDEAL', 'payoneer-checkout');
    }
}
