<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Analytics;

use Syde\Vendor\Inpsyde\Modularity\Module\ExecutableModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ExtendingModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Syde\Vendor\Inpsyde\Modularity\Module\ServiceModule;
use Syde\Vendor\Psr\Container\ContainerInterface;
use WC_Cart;
use WC_Order;
use WC_Payment_Gateway;
class AnalyticsModule implements ServiceModule, ExtendingModule, ExecutableModule
{
    use ModuleClassNameIdTrait;
    public function run(ContainerInterface $container): bool
    {
        add_action('woocommerce_init', function () use ($container): void {
            $analyticsEnabled = $container->get('analytics.analytics_enabled');
            if (!$analyticsEnabled) {
                return;
            }
            $this->registerPluginActivatedListener($container);
            if ($container->get('analytics.is_live_mode')) {
                $this->registerPaymentRelatedListeners($container);
            }
            /** @var array<string, array<string, mixed>> $trackedHooksConfig */
            $trackedHooksConfig = apply_filters('payoneer-checkout.analytics_events', $container->get('analytics.analytics_events'));
            $eventHandler = $container->get('analytics.event_handler');
            assert($eventHandler instanceof AnalyticsEventHandlerInterface);
            $this->setUpTrackedHooksHandling($eventHandler, $trackedHooksConfig);
        });
        return \true;
    }
    /**
     * @param ContainerInterface $container
     */
    protected function registerPaymentRelatedListeners(ContainerInterface $container): void
    {
        $this->registerCheckoutViewedListener($container);
        $this->registerOrderStatusChangedToProcessingListener($container);
        $this->registerOrderStatusChangedToFailedListener($container);
        add_action('wp', function () use ($container): void {
            $this->registerOrderCreatedFromCheckoutListener($container);
            $this->registerOrderFailedFromCheckoutListener($container);
            $this->registerOrderPayFormSubmittedListener($container);
        });
    }
    /**
     * @inheritDoc
     */
    public function services(): array
    {
        static $services;
        if ($services === null) {
            $services = require_once dirname(__DIR__) . '/inc/services.php';
        }
        /** @var callable(): array<string, callable(ContainerInterface $container):mixed> $services */
        return $services();
    }
    /**
     * @inheritDoc
     */
    public function extensions(): array
    {
        static $extensions;
        if ($extensions === null) {
            $extensions = require_once dirname(__DIR__) . '/inc/extensions.php';
        }
        /** @var callable(): array<string, callable(mixed $service, \Psr\Container\ContainerInterface $container):mixed> $extensions */
        return $extensions();
    }
    public function registerOrderCreatedFromCheckoutListener(ContainerInterface $container): void
    {
        add_filter('woocommerce_payment_successful_result', function ($result, $orderId) use ($container) {
            $order = wc_get_order($orderId);
            $gaSessionIdProvider = $container->get('analytics.ga_session_id_provider');
            assert(is_callable($gaSessionIdProvider));
            $gaSessionFieldName = (string) $container->get('analytics.ga_session_id_order_field_name');
            if (!$order instanceof WC_Order) {
                return $result;
            }
            $this->ensureOrderHasGaSessionId($order, $gaSessionFieldName, $gaSessionIdProvider);
            $gaSessionId = $order->get_meta($gaSessionFieldName);
            $paymentMethod = $this->getChosenPaymentMethod($container);
            do_action('payoneer-checkout.order_created_from_checkout', ['BILLING_COUNTRY' => $order->get_billing_country(), 'CURRENCY' => $order->get_currency(), 'PAYMENT_METHOD_ID' => $paymentMethod, 'TOTAL_AMOUNT' => (float) $order->get_total(''), 'GA_SESSION_ID' => $gaSessionId, 'STORE_CODE' => (string) $container->get('analytics.merchant_division')]);
            return $result;
        }, \PHP_INT_MAX, 2);
    }
    public function registerOrderFailedFromCheckoutListener(ContainerInterface $container): void
    {
        add_action('shutdown', function () use ($container): void {
            if (!filter_input(\INPUT_POST, 'woocommerce-process-checkout-nonce', \FILTER_SANITIZE_SPECIAL_CHARS)) {
                return;
            }
            if (did_action('payoneer-checkout.order_created_from_checkout')) {
                return;
            }
            $wcSession = $container->get('analytics.wc.session');
            assert($wcSession instanceof \WC_Session);
            do_action('payoneer-checkout.order_failed_from_checkout', ['BILLING_COUNTRY' => filter_input(\INPUT_POST, 'billing_country', \FILTER_CALLBACK, ['options' => 'strip_tags']), 'PAYMENT_METHOD_ID' => $this->getChosenPaymentMethod($container), 'CURRENCY' => $container->get('analytics.store_currency'), 'TOTAL_AMOUNT' => $this->getCartTotal($container), 'STORE_CODE' => (string) $container->get('analytics.merchant_division')], \PHP_INT_MAX);
        });
    }
    public function registerOrderPayFormSubmittedListener(ContainerInterface $container): void
    {
        if (!filter_input(\INPUT_POST, 'woocommerce-pay-nonce', \FILTER_SANITIZE_SPECIAL_CHARS)) {
            return;
        }
        $orderId = $container->get('analytics.order_under_payment.id');
        $gaSessionIdProvider = $container->get('analytics.ga_session_id_provider');
        assert(is_callable($gaSessionIdProvider));
        $gaSessionFieldName = (string) $container->get('analytics.ga_session_id_order_field_name');
        $order = wc_get_order($orderId);
        if (!$order instanceof WC_Order) {
            return;
        }
        $this->ensureOrderHasGaSessionId($order, $gaSessionFieldName, $gaSessionIdProvider);
        $gaSessionId = $order->get_meta($gaSessionFieldName);
        $paymentMethod = $this->getChosenPaymentMethod($container);
        do_action('payoneer-checkout.pay_for_order_form_submitted', ['BILLING_COUNTRY' => $order->get_billing_country(), 'CURRENCY' => $order->get_currency(), 'TOTAL_AMOUNT' => (float) $order->get_total(''), 'PAYMENT_METHOD_ID' => $paymentMethod, 'GA_SESSION_ID' => $gaSessionId, 'STORE_CODE' => (string) $container->get('analytics.merchant_division')]);
    }
    public function registerOrderStatusChangedToProcessingListener(ContainerInterface $container): void
    {
        add_action(
            'woocommerce_order_status_processing',
            /**
             * @psalm-suppress UnusedClosureParam
             */
            static function (int $orderId, WC_Order $order) use ($container): void {
                do_action('payoneer-checkout.order_status_changed_to_processing', ['BILLING_COUNTRY' => $order->get_billing_country(), 'CURRENCY' => $order->get_currency(), 'TOTAL_AMOUNT' => (float) $order->get_total(''), 'PAYMENT_METHOD_ID' => $order->get_payment_method(), 'STORE_CODE' => (string) $container->get('analytics.merchant_division')]);
            },
            10,
            2
        );
    }
    public function registerOrderStatusChangedToFailedListener(ContainerInterface $container): void
    {
        add_action(
            'woocommerce_order_status_failed',
            /**
             * @psalm-suppress UnusedClosureParam
             */
            static function (int $orderId, WC_Order $order) use ($container): void {
                do_action('payoneer-checkout.order_status_changed_to_failed', ['BILLING_COUNTRY' => $order->get_billing_country(), 'PAYMENT_METHOD_ID' => $order->get_payment_method(), 'CURRENCY' => $order->get_currency(), 'TOTAL_AMOUNT' => (float) $order->get_total(''), 'STORE_CODE' => (string) $container->get('analytics.merchant_division')]);
            },
            10,
            2
        );
    }
    /**
     * Do action after plugin activated.
     */
    public function registerPluginActivatedListener(ContainerInterface $container): void
    {
        add_action('wp', static function () use ($container): void {
            if (get_option('payoneer-checkout_plugin_activated')) {
                delete_option('payoneer-checkout_plugin_activated');
                do_action('payoneer-checkout_plugin_activated', ['PLUGIN_VERSION' => (string) $container->get('analytics.plugin_version_string')]);
            }
        });
    }
    /**
     * Do action when checkout page viewed.
     */
    public function registerCheckoutViewedListener(ContainerInterface $container): void
    {
        add_action('shutdown', function () use ($container): void {
            if (!$container->get('analytics.is_checkout')) {
                return;
            }
            if ($container->get('analytics.is_order_received_page')) {
                return;
            }
            if ($container->get('analytics.is_ajax')) {
                return;
            }
            $total = $container->get('analytics.is_checkout_pay_page') ? $this->getTotalOnOrderPayPage($container) : $this->getCartTotal($container);
            do_action('payoneer-checkout.checkout_page_viewed', ['CURRENCY' => $container->get('analytics.store_currency'), 'TOTAL_AMOUNT' => $total, 'PAGE_LOCATION' => (string) $container->get('analytics.http.current_url'), 'PAYMENT_METHOD_ID' => $this->getChosenPaymentMethod($container), 'STORE_CODE' => (string) $container->get('analytics.merchant_division')]);
        });
    }
    protected function getTotalOnOrderPayPage(ContainerInterface $container): float
    {
        $orderUnderPayment = wc_get_order($container->get('analytics.order_under_payment.id'));
        if (!$orderUnderPayment instanceof WC_Order) {
            return 0.0;
        }
        return (float) $orderUnderPayment->get_total('');
    }
    /**
     * Try to detect used payment method for checkout.
     *
     * @param ContainerInterface $container
     *
     * @return string
     */
    protected function getChosenPaymentMethod(ContainerInterface $container): string
    {
        $postedPaymentMethod = $this->getPostedPaymentMethod();
        if ($postedPaymentMethod) {
            return $postedPaymentMethod;
        }
        $selectedPaymentMethodFromGateways = $this->getSelectedPaymentMethodFromGateways($container);
        if ($selectedPaymentMethodFromGateways) {
            return $selectedPaymentMethodFromGateways;
        }
        return '';
    }
    protected function getPostedPaymentMethod(): string
    {
        return (string) filter_input(\INPUT_POST, 'payment_method', \FILTER_CALLBACK, ['options' => 'strip_tags']);
    }
    protected function getSelectedPaymentMethodFromGateways(ContainerInterface $container): string
    {
        $wooCommerce = $container->get('wc');
        if (!$wooCommerce instanceof \WooCommerce) {
            return '';
        }
        /** @var WC_Payment_Gateway[] $paymentGateways */
        $paymentGateways = $wooCommerce->payment_gateways()->payment_gateways();
        foreach ($paymentGateways as $gateway) {
            if ($gateway->chosen) {
                return $gateway->id;
            }
        }
        return '';
    }
    protected function getCartTotal(ContainerInterface $container): float
    {
        if (!$container->get('wc.cart.is-available')) {
            return 0.0;
        }
        $cart = $container->get('analytics.wc.cart');
        if (!$cart instanceof WC_Cart) {
            return 0.0;
        }
        $total = $cart->get_total('');
        return (float) $total;
    }
    protected function ensureOrderHasGaSessionId(WC_Order $order, string $gaSessionFieldName, callable $gaSessionIdProvider): void
    {
        $gaSessionId = $order->get_meta($gaSessionFieldName);
        if (!$gaSessionId) {
            $gaSessionId = (string) $gaSessionIdProvider();
            $order->update_meta_data($gaSessionFieldName, $gaSessionId);
            $order->save();
        }
    }
    /**
     * @param AnalyticsEventHandlerInterface $eventHandler
     * @param array<string, array<string, mixed>> $trackedHooksConfig
     */
    public function setUpTrackedHooksHandling(AnalyticsEventHandlerInterface $eventHandler, array $trackedHooksConfig): void
    {
        foreach ($trackedHooksConfig as $hookName => $payloadConfig) {
            add_action(
                $hookName,
                /**
                 * @param array<string, string> $context
                 */
                static function (array $context) use ($payloadConfig, $eventHandler): void {
                    $eventHandler->handleAnalyticsEvent($payloadConfig, $context);
                }
            );
        }
    }
}
