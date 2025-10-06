<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Definition;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\ListCondition\MatchNetworkCodeCondition;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\ListCondition\MatchNetworkGroupingCondition;
class Multibanco extends AbstractPayoneerPaymentMethodDefinition
{
    public function id(): string
    {
        return 'payoneer-multibanco';
    }
    public function availabilityListConditions(): iterable
    {
        yield new MatchNetworkGroupingCondition('DIRECT_DEBIT');
        yield new MatchNetworkCodeCondition('MULTIBANCO');
    }
    public function dropIn(): string
    {
        return 'multibanco';
    }
    public function fallbackTitle(): string
    {
        return __('Multibanco', 'payoneer-checkout');
    }
}
