<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Definition;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\ListCondition\MatchNetworkCodeCondition;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\ListCondition\MatchNetworkGroupingCondition;
class Klarna extends AbstractPayoneerPaymentMethodDefinition
{
    public function id(): string
    {
        return 'payoneer-klarna';
    }
    public function availabilityListConditions(): iterable
    {
        yield new MatchNetworkGroupingCondition('DIRECT_DEBIT');
        yield new MatchNetworkCodeCondition('KLARNA');
    }
    public function dropIn(): string
    {
        return 'klarna';
    }
    public function fallbackTitle(): string
    {
        return __('Klarna', 'payoneer-checkout');
    }
}
