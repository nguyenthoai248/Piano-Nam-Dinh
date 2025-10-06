<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Definition;

use Syde\Vendor\Dhii\Services\Factory;
use Syde\Vendor\Inpsyde\PaymentGateway\PaymentProcessorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\HostedPayment\PaymentProcessor\HostedPaymentProcessor;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\ListCondition\NoopListCondition;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\PaymentProcessor\PayoneerCommonPaymentProcessor;
use Syde\Vendor\Psr\Container\ContainerInterface;
class HostedPaymentPage extends AbstractPayoneerPaymentMethodDefinition
{
    public function id(): string
    {
        return 'payoneer-hosted';
    }
    /**
     * In Payoneer, methods are not enabled/disabled individually by the merchant.
     * We register them all unconditionally.
     * Whether they are available for checkout is determined by
     * the LIST response contents.
     *
     * @param ContainerInterface $container
     *
     * @return bool
     */
    public function isEnabled(ContainerInterface $container): bool
    {
        $instance = $this->fetchInstance();
        return $instance->get_option('enabled') === 'yes' && $instance->get_option('payment_flow') === 'hosted';
    }
    public function paymentProcessor(ContainerInterface $container): PaymentProcessorInterface
    {
        $paymentProcessor = (new Factory(['payment_methods.common_payment_processor', 'list_session.manager', 'wp.is_rest_api_request'], static function (PayoneerCommonPaymentProcessor $common, ListSessionProvider $listSessionProvider): PaymentProcessorInterface {
            return new HostedPaymentProcessor($common, $listSessionProvider);
        }))($container);
        assert($paymentProcessor instanceof PaymentProcessorInterface);
        return $paymentProcessor;
    }
    public function availabilityListConditions(): iterable
    {
        return [new NoopListCondition()];
    }
    public function dropIn(): string
    {
        return 'hosted';
    }
    public function fallbackTitle(): string
    {
        return __('Hosted payment page', 'payoneer-checkout');
    }
}
