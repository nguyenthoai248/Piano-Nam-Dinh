<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Definition;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\ListCondition\MatchNetworkCodeCondition;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\ListCondition\MatchNetworkGroupingCondition;
class P24 extends AbstractPayoneerPaymentMethodDefinition
{
    public function id(): string
    {
        return 'payoneer-p24';
    }
    public function availabilityListConditions(): iterable
    {
        yield new MatchNetworkGroupingCondition('DIRECT_DEBIT');
        yield new MatchNetworkCodeCondition('P24');
    }
    public function dropIn(): string
    {
        return 'p24';
    }
    public function fallbackTitle(): string
    {
        return __('P24', 'payoneer-checkout');
    }
}
