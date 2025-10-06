<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Definition;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\ListCondition\ListConditionInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\ListCondition\MatchNetworkCodeCondition;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\ListCondition\MatchNetworkGroupingCondition;
class Affirm extends AbstractPayoneerPaymentMethodDefinition
{
    public function id(): string
    {
        return 'payoneer-affirm';
    }
    /**
     * @return iterable<ListConditionInterface>
     */
    public function availabilityListConditions(): iterable
    {
        yield new MatchNetworkGroupingCondition('DIRECT_DEBIT');
        yield new MatchNetworkCodeCondition('AFFIRM');
    }
    public function dropIn(): string
    {
        return 'affirm';
    }
    public function fallbackTitle(): string
    {
        return __('Affirm', 'payoneer-checkout');
    }
}
