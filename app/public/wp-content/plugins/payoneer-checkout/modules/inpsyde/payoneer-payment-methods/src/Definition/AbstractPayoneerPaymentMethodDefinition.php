<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Definition;

use Syde\Vendor\Dhii\Services\Factories\Constructor;
use Syde\Vendor\Dhii\Services\Factory;
use Syde\Vendor\Inpsyde\PaymentGateway\DefaultIconsRenderer;
use Syde\Vendor\Inpsyde\PaymentGateway\GatewayIconsRendererInterface;
use Syde\Vendor\Inpsyde\PaymentGateway\IconProviderInterface;
use Syde\Vendor\Inpsyde\PaymentGateway\Method\DefaultPaymentMethodDefinitionTrait;
use Syde\Vendor\Inpsyde\PaymentGateway\Method\PaymentMethodDefinition;
use Syde\Vendor\Inpsyde\PaymentGateway\PaymentFieldsRendererInterface;
use Syde\Vendor\Inpsyde\PaymentGateway\PaymentProcessorInterface;
use Syde\Vendor\Inpsyde\PaymentGateway\RefundProcessorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\PaymentFieldsRenderer\CompoundPaymentFieldsRenderer;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\PaymentFieldsRendererFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\PaymentProcessor\EmbeddedPaymentProcessor;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\HostedPayment\PaymentProcessor\HostedPaymentProcessor;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionManager;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\AvailabilityCallback\AvailabilityCallbackInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\AvailabilityCallback\CompoundAvailabilityCallback;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\AvailabilityCallback\ConditionalCallbackDecorator;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\AvailabilityCallback\FilteredAvailabilityCallback;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\AvailabilityCallback\ListConditionAvailabilityCallback;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\GatewayIconsRenderer\IconProviderFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\ListCondition\ListConditionInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\PaymentProcessor\PayoneerCommonPaymentProcessor;
use Syde\Vendor\Psr\Container\ContainerExceptionInterface;
use Syde\Vendor\Psr\Container\ContainerInterface;
use WC_Payment_Gateway;
abstract class AbstractPayoneerPaymentMethodDefinition implements PaymentMethodDefinition
{
    use DefaultPaymentMethodDefinitionTrait;
    /**
     * The handle of the dropIn component used by the JS WebSDK
     *
     * @return string
     */
    abstract public function dropIn(): string;
    /**
     * The human-friendly payment method title to use when no custom title was configured.
     *
     * @return string
     */
    abstract public function fallbackTitle(): string;
    public function title(ContainerInterface $container): string
    {
        return (string) (new Factory(['wc.is_checkout', 'payment_methods.is_live_mode', 'wc.is_store_api_request'], function (bool $isCheckout, bool $isLiveMode, bool $isStoreApiRequest): string {
            $gateway = $this->fetchInstance();
            $baseName = (string) $gateway->get_option('title-' . $this->id());
            if ($baseName === '') {
                $baseName = $this->fallbackTitle();
            }
            if (($isCheckout || $isStoreApiRequest) && !$isLiveMode) {
                $baseName = __('Test:', 'payoneer-checkout') . ' ' . $baseName;
            }
            return $baseName;
        }))($container);
    }
    /**
     * In Payoneer, methods are not enabled/disabled individually by the merchant.
     * We register them all unconditionally.
     * Whether they are available for checkout is determined by
     * the LIST response contents.
     * For most payment methods, we simply need to check if we have embedded mode configured
     *
     * @param ContainerInterface $container
     *
     * @return bool
     */
    public function isEnabled(ContainerInterface $container): bool
    {
        $instance = $this->fetchInstance();
        return $instance->get_option('enabled') === 'yes' && $instance->get_option('payment_flow') === 'embedded';
    }
    public function methodTitle(ContainerInterface $container): string
    {
        return sprintf(__('Payoneer Checkout - %s', 'payoneer-checkout'), $this->fallbackTitle());
    }
    public function methodDescription(ContainerInterface $container): string
    {
        return (string) (new Factory(['payment_methods.payoneer-checkout.method_description.payments_settings_page', 'payment_methods.payoneer-checkout.method_description.settings_page', 'payoneer_settings.is_payments_settings_page'], static function (string $paymentsSettingsPageDescription, string $settingsPageDescription, bool $isPaymentsSettingsPage): string {
            if ($isPaymentsSettingsPage) {
                return $paymentsSettingsPageDescription;
            }
            return $settingsPageDescription;
        }))($container);
    }
    public function formFields(ContainerInterface $container): array
    {
        return (array) $container->get('payoneer_settings.settings_fields');
    }
    public function optionKey(ContainerInterface $container): string
    {
        return 'woocommerce_payoneer-checkout_settings';
    }
    public function description(ContainerInterface $container): string
    {
        $gateway = $this->fetchInstance();
        $optionKey = 'description-' . $this->id();
        return (string) $gateway->get_option($optionKey);
    }
    public function paymentProcessor(ContainerInterface $container): PaymentProcessorInterface
    {
        $paymentProcessor = (new Factory(['payment_methods.common_payment_processor', 'checkout.payment_flow_override_flag', 'wp.is_rest_api_request', 'checkout.payment_flow_override_flag.is_set', 'list_session.manager'], static function (PayoneerCommonPaymentProcessor $common, string $hostedModeOverrideFlag, bool $isRestRequest, bool $hostedModeOverrideFlagIsSet, ListSessionProvider $listSessionProvider): PaymentProcessorInterface {
            if ($hostedModeOverrideFlagIsSet) {
                return new HostedPaymentProcessor($common, $listSessionProvider);
            }
            return new EmbeddedPaymentProcessor($common, $hostedModeOverrideFlag, $isRestRequest);
        }))($container);
        /**
         * Just to make psalm happy.
         *
         * @todo: try to make Factory return type understandable for psalm using templates on
         *      a Factory wrapper. One for the whole project should be enough. Alternatively, make
         *      a contribution to the dhii/services package, providing psalm-friendly docblocks.
         */
        assert($paymentProcessor instanceof PaymentProcessorInterface);
        return $paymentProcessor;
    }
    public function refundProcessor(ContainerInterface $container): RefundProcessorInterface
    {
        /**
         * We have a single RefundProcessor instance that is relevant for all payment methods.
         *
         * This instance has an internal state which must be preserved throughout the request,
         * so it's important to only create one instance of this class.
         */
        $refundProcessor = $container->get('payment_methods.common.refund_processor');
        assert($refundProcessor instanceof RefundProcessorInterface);
        return $refundProcessor;
    }
    public function paymentMethodIconProvider(ContainerInterface $container): IconProviderInterface
    {
        $factory = (new Constructor(IconProviderFactory::class, ['core.main_plugin_file', 'payment_methods.path.assets', 'list_session.can_try_create_list.callable', 'list_session.manager', 'payment_methods.network_icon_map']))($container);
        try {
            $defaultIcons = $container->get("payment_methods.{$this->id()}.default_icons");
            assert(is_array($defaultIcons));
        } catch (ContainerExceptionInterface $exception) {
            $defaultIcons = [];
        }
        assert($factory instanceof IconProviderFactory);
        return $factory->create($defaultIcons);
    }
    public function gatewayIconsRenderer(ContainerInterface $container): GatewayIconsRendererInterface
    {
        $iconProvider = $container->get("payment_gateway.{$this->id()}.method_icon_provider");
        assert($iconProvider instanceof IconProviderInterface);
        return new DefaultIconsRenderer($iconProvider);
    }
    public function icon(ContainerInterface $container): string
    {
        return '';
    }
    public function supports(ContainerInterface $container): array
    {
        return ['products', 'refunds'];
    }
    public function hasFields(ContainerInterface $container): bool
    {
        return \true;
    }
    public function paymentFieldsRenderer(ContainerInterface $container): PaymentFieldsRendererInterface
    {
        $description = $this->fetchInstance()->get_description();
        $renderers = PaymentFieldsRendererFactory::forComponent($this->dropIn(), $container, $description);
        return new CompoundPaymentFieldsRenderer(...$renderers);
    }
    public function availabilityCallback(ContainerInterface $container): callable
    {
        $callback = (new Factory(['payment_methods.availability_callback.live_mode', 'list_session.manager', 'embedded_payment.ajax_order_pay.is_ajax_order_pay', 'payment_methods.availability_callback.checkout_predicate'], function (AvailabilityCallbackInterface $liveModeCallback, ListSessionManager $listSessionManager, bool $isAjaxOrderPay, callable $checkoutPredicate): callable {
            $callbacks = [$liveModeCallback];
            foreach ($this->availabilityListConditions() as $condition) {
                $callbacks[] = new ConditionalCallbackDecorator($checkoutPredicate, new ListConditionAvailabilityCallback($listSessionManager, $condition, $isAjaxOrderPay));
            }
            $compound = new CompoundAvailabilityCallback(...$callbacks);
            return new FilteredAvailabilityCallback($compound);
        }))($container);
        assert(is_callable($callback));
        /**
         * @psalm-var callable(WC_Payment_Gateway):bool
         */
        return $callback;
    }
    abstract public function id(): string;
    /**
     * @return iterable<ListConditionInterface>
     */
    abstract public function availabilityListConditions(): iterable;
}
