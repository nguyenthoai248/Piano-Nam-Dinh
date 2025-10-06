<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Definition;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\ListCondition\MatchNetworkGroupingCondition;
use Syde\Vendor\Psr\Container\ContainerInterface;
class CreditCards extends AbstractPayoneerPaymentMethodDefinition
{
    public function id(): string
    {
        return 'payoneer-checkout';
    }
    public function dropIn(): string
    {
        return 'cards';
    }
    public function orderButtonText(ContainerInterface $container): string
    {
        return __('Pay', 'payoneer-checkout');
    }
    public function availabilityListConditions(): iterable
    {
        yield new MatchNetworkGroupingCondition('CREDIT_CARD');
    }
    public function fallbackTitle(): string
    {
        return __('Credit / Debit cards', 'payoneer-checkout');
    }
}
