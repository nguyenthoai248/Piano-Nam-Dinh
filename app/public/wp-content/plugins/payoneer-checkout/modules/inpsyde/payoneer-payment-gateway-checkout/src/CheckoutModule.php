<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout;

use Exception;
use Syde\Vendor\Inpsyde\Modularity\Module\ExecutableModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ExtendingModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Syde\Vendor\Inpsyde\Modularity\Module\ServiceModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\MisconfigurationDetector\MisconfigurationDetectorInterface;
use Syde\Vendor\Psr\Container\ContainerInterface;
use RuntimeException;
use WC_Order;
class CheckoutModule implements ServiceModule, ExecutableModule, ExtendingModule
{
    use ModuleClassNameIdTrait;
    /**
     * Interaction codes signalizing payment failure.
     */
    protected const FAILED_PAYMENT_INTERACTION_CODES = ['RETRY', 'ABORT', 'TRY_OTHER_ACCOUNT', 'TRY_OTHER_NETWORK'];
    /**
     * @var array<string, callable>
     * @psalm-var array<string, callable(ContainerInterface): mixed>
     */
    protected $services;
    /**
     * @var array<string, callable>
     * @psalm-var array<string, callable(mixed $service, \Psr\Container\ContainerInterface
     *     $container):mixed>
     */
    protected $extensions;
    public function __construct()
    {
        $moduleRootDir = dirname(__FILE__, 2);
        $this->services = (require "{$moduleRootDir}/inc/services.php")();
        $this->extensions = (require "{$moduleRootDir}/inc/extensions.php")();
    }
    /**
     * @inheritDoc
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function services(): array
    {
        return $this->services;
    }
    /**
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     * @inheritDoc
     */
    public function run(ContainerInterface $container): bool
    {
        $this->registerCheckoutSetup($container);
        $this->registerCacheSaltUpdating($container);
        $this->setupFiringPaymentCompleteAction($container);
        $this->registerAddingLiveModeNotice($container);
        $notificationReceivedOptionName = (string) $container->get('checkout.notification_received.option_name');
        $this->addIncomingWebhookListener($notificationReceivedOptionName);
        $this->addCreateListSessionFailedListener($container);
        return \true;
    }
    protected function registerCheckoutSetup(ContainerInterface $container): void
    {
        add_action('woocommerce_init', function () use ($container) {
            $gatewayEnabled = (bool) $container->get('checkout.payment_gateway.is_enabled');
            if ($gatewayEnabled) {
                $this->setupCheckoutActions($container);
                do_action('payoneer-checkout.init_checkout');
            }
        });
    }
    protected function registerCacheSaltUpdating(ContainerInterface $container): void
    {
        $saltOptionName = $container->get('checkout.list_session_manager.cache_key.salt.option_name');
        $eventsToUpdateSaltOn = $container->get('checkout.list_session_manager.cache_key.salt.update_on_events');
        assert(is_string($saltOptionName));
        assert(is_array($eventsToUpdateSaltOn));
        /** @psalm-var string[] $eventsToUpdateSaltOn */
        foreach ($eventsToUpdateSaltOn as $event) {
            add_action($event, static function () use ($saltOptionName): void {
                delete_option($saltOptionName);
            });
        }
    }
    /**
     * We are not in control of the CHARGE call, but we need the CHARGE longId
     * for refunds via webhooks
     * Luckily, we receive that ID as a GET parameter on the redirect to the success-Url
     *
     * Note that we will also store the CHARGE when we process its notification,
     * but doing it here might be quicker in some cases
     *
     * @param WC_Order $order
     * @param string $metaKey
     *
     * @return void
     */
    protected function onThankYouPage(WC_Order $order, string $metaKey)
    {
        $chargeLongId = filter_input(\INPUT_GET, 'longId', \FILTER_CALLBACK, ['options' => 'sanitize_text_field']);
        if ($chargeLongId && !$order->meta_exists($metaKey)) {
            /**
             * We synchronously store the CHARGE longId if it does not exist yet.
             */
            $order->update_meta_data($metaKey, (string) $chargeLongId);
            $order->save();
        }
        /**
         * Between WC_Payment_Gateway::process_payment() and the thankyou-page, we might process
         * a number of webhooks that cause different order statuses:
         * - RISK may fail, leading to a 'failed' order (the customer can retry though)
         * - CHARGE might succeed, leading to a 'processing' order
         * - Both might happen one after another
         *
         * We cannot expect the webhooks to arrive before or after the redirect to the thankyou-page
         * It could be that the webhook(s) hit us earlier than we get to render the thank-you page.
         * It could also be that webhooks are still pending.
         *
         * The only thing that's certain is that there has been a successful payment:
         * We know that our gateway has been used, but we don't redirect to thank-you ourselves.
         * This URL is only ever passed to Payoneer as the 'successUrl'
         *
         * So we're here because payment succeeded, but we currently cannot trust the order status.
         * In addition, a potential 'failed' order status will cause WooCommerce to print a notice
         * about a declined transaction and urges customers to pay again.
         *
         * So first, we'll check if the order currently has an undesired state...
         */
        if (!$order->has_status(['on-hold', 'processing', 'completed'])) {
            /**
             * Now we need to trick WooCommerce into thinking the order is actually 'on-hold'
             * by adding a temporary filter.
             * We don't want to persistently update the order in the database here:
             *
             * 1) Because it would not even work: The thankyou.php template that is currently
             * being rendered already works against a fully populated instance of our order,
             * so it will not receive our status update.
             *
             * 2) Because we want webhooks to act as the source of truth. We're basically doing
             * "weird hacks" here and cannot risk letting this result in an inconsistent order state
             *
             * 3) With very unlucky timing, the webhook is being processed in parallel _right now_
             * Then we would potentially be overwriting a previous status update,
             * leading to very confusing order notes & status
             *
             * With these hooks, we temporarily ensure that even the order instance
             * used by the thankyou.php template returns the 'on-hold' status.
             *
             * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
             */
            $orderHasStatusFilter = static function (bool $hasStatus, WC_Order $currentOrder, $status) use ($order): bool {
                if ($currentOrder->get_id() !== $order->get_id()) {
                    return $hasStatus;
                }
                return $status === 'on-hold';
            };
            add_filter('woocommerce_order_has_status', $orderHasStatusFilter, 10, 3);
            $orderGetStatusFilter = static function (string $status, WC_Order $currentOrder) use ($order): string {
                if ($currentOrder->get_id() !== $order->get_id()) {
                    return $status;
                }
                return 'on-hold';
            };
            add_filter('woocommerce_order_get_status', $orderGetStatusFilter, 10, 2);
            /**
             * After the thankyou.php template has rendered, we remove the hooks again lest we
             * override ANY subsequent call to $order->has_status() during further processing
             */
            add_action('woocommerce_thankyou', static function () use ($orderHasStatusFilter, $orderGetStatusFilter) {
                remove_filter('woocommerce_order_has_status', $orderHasStatusFilter);
                remove_filter('woocommerce_order_get_status', $orderGetStatusFilter);
            });
        }
    }
    /**
     * Payoneer might redirect us to the 'cancelUrl' if the 3DS challenge fails.
     * In this case, it is very likely that the webhook informing us about the failed transaction
     * arrives too late: The order will still be 'on-hold',
     * causing WooCommerce to block further payment attempts.
     *
     * So we inspect the GET parameters here to synchronously update the order status
     *
     * @param WC_Order $order
     *
     * @return void
     */
    protected function beforeOrderPay(WC_Order $order): void
    {
        $interactionCode = filter_input(\INPUT_GET, 'interactionCode', \FILTER_CALLBACK, ['options' => 'sanitize_text_field']);
        if (!$interactionCode || $order->is_paid()) {
            return;
        }
        if (!in_array($interactionCode, self::FAILED_PAYMENT_INTERACTION_CODES, \true)) {
            return;
        }
        $interactionReason = filter_input(\INPUT_GET, 'interactionReason', \FILTER_CALLBACK, ['options' => 'sanitize_text_field']);
        $isGet = isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET';
        if ($isGet) {
            $this->addCustomerNotice((string) $interactionReason);
        }
        if (!$order->has_status('failed')) {
            /**
             * We always need the message, but we do not always need to update the status:
             * The webhook might have arrived already.
             */
            /**
             * We currently do not handle webhooks about 'session' ABORT
             * So for now, let's just add a message here
             */
            if ($interactionCode === 'ABORT') {
                $order->add_order_note(
                    /* translators: When detecting an ABORT interaction code upon redirecting to the cancelUrl */
                    __('Payment has been aborted', 'payoneer-checkout')
                );
                do_action('payoneer-checkout.payment_aborted', $order);
            }
            $order->update_status('failed');
            $order->save();
        }
    }
    protected function addCustomerNotice(string $interactionReason): void
    {
        $errorMessage = __('Payment failed. Please try again', 'payoneer-checkout');
        if ($interactionReason === 'CUSTOMER_ABORT') {
            /* translators: Notice when redirecting to cancelUrl (after failed 3DS challenge or customer abort) */
            $errorMessage = __('Payment canceled. Please try again or choose another payment method.', 'payoneer-checkout');
        }
        wc_add_notice($errorMessage, 'error');
    }
    /**
     * @inheritDoc
     */
    public function extensions(): array
    {
        return $this->extensions;
    }
    protected function setupCheckoutActions(ContainerInterface $container): void
    {
        $payoneerGatewayIds = $container->get('payment_gateways');
        assert(is_array($payoneerGatewayIds));
        add_action('wp', function () use ($container, $payoneerGatewayIds) {
            if (!$container->get('wc.is_checkout_pay_page')) {
                return;
            }
            $orderId = get_query_var('order-pay');
            $wcOrder = wc_get_order($orderId);
            if (!$wcOrder instanceof WC_Order) {
                return;
            }
            if (!in_array($wcOrder->get_payment_method(), $payoneerGatewayIds, \true)) {
                return;
            }
            $this->beforeOrderPay($wcOrder);
        }, 0);
        add_action('woocommerce_before_thankyou', function (int $orderId) use ($container) {
            $wcOrder = wc_get_order($orderId);
            if (!$wcOrder instanceof WC_Order) {
                return;
            }
            $payoneerGatewayIds = $container->get('payment_gateways');
            assert(is_array($payoneerGatewayIds));
            if (!in_array($wcOrder->get_payment_method(), $payoneerGatewayIds, \true)) {
                return;
            }
            $chargeIdFieldName = (string) $container->get('inpsyde_payment_gateway.charge_id_field_name');
            $this->onThankYouPage($wcOrder, $chargeIdFieldName);
        });
        /**
         * This is a temporary solution because we need a little styling for the CC icons.
         * The icons are added by this module so they should be styled by this module
         * TODO supply a proper css file for this. Rework markup into something more responsive
         */
        $paymentMethodsIconsCss = $container->get('checkout.gateway_icon_elements_css');
        assert(is_string($paymentMethodsIconsCss));
        add_action('wp', static function () use ($paymentMethodsIconsCss) {
            if (is_checkout()) {
                $handle = 'payoneer-checkout-base-css';
                wp_register_style($handle, \false, [], '*');
                wp_enqueue_style($handle);
                wp_add_inline_style($handle, $paymentMethodsIconsCss);
            }
        });
    }
    protected function setupFiringPaymentCompleteAction(ContainerInterface $container): void
    {
        add_action('woocommerce_pre_payment_complete', static function ($orderId) use ($container): void {
            $order = wc_get_order($orderId);
            if (!$order instanceof WC_Order) {
                throw new RuntimeException(sprintf('Cannot get order by provided ID %1$s', (string) $orderId));
            }
            if ($order->is_paid()) {
                return;
            }
            $orderPaymentGateway = $order->get_payment_method();
            $payoneerPaymentGateways = $container->get('payment_gateways');
            assert(is_array($payoneerPaymentGateways));
            if (!in_array($orderPaymentGateway, $payoneerPaymentGateways, \true)) {
                return;
            }
            $chargeIdFieldName = $container->get('core.payment_gateway.order.charge_id_field_name');
            assert(is_string($chargeIdFieldName));
            $chargeId = $order->get_meta($chargeIdFieldName, \true);
            do_action($orderPaymentGateway . '_payment_processing_success', ['chargeId' => $chargeId]);
        });
    }
    /**
     * @param string $settingsPageUrl
     *
     * @return void
     */
    protected function registerAddingLiveModeNotice(ContainerInterface $container): void
    {
        add_action('all_admin_notices', static function () use ($container): void {
            $liveMode = (bool) $container->get('inpsyde_payment_gateway.is_live_mode');
            $notificationReceived = (bool) $container->get('checkout.notification_received');
            if ($liveMode || $notificationReceived) {
                return;
            }
            $settingsPageUrl = (string) $container->get('inpsyde_payment_gateway.settings_page_url');
            $class = 'notice notice-warning';
            $aTagOpening = sprintf('<a href="%1$s">', $settingsPageUrl);
            $disableTestMode = sprintf(
                /* translators: %1$s, %2$s and %3$s are replaced with the opening and closing 'a' tags */
                esc_html__('Enter valid Test credentials and Save settings to receive a payment notification and unlock Live mode checkbox. You can %1$srefresh%2$s the page to check if a payment notification has been already received and Live mode checkbox is unlocked.', 'payoneer-checkout'),
                $aTagOpening,
                '</a>',
                '<a href="">'
            );
            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), wp_kses($disableTestMode, ['a' => ['href' => []]], ['http', 'https']));
        }, 12);
    }
    /**
     * Set option as a flag when status notification received.
     *
     * @param string $optionName
     */
    protected function addIncomingWebhookListener(string $optionName): void
    {
        add_action('payoneer-checkout.webhook_request', static function () use ($optionName): void {
            update_option($optionName, 'yes');
        });
    }
    /**
     * Add listener hiding payment gateway if failed to create LIST because of authorization issue.
     *
     * @param ContainerInterface $container
     *
     * @return void
     */
    protected function addCreateListSessionFailedListener(ContainerInterface $container): void
    {
        /**
         * Make our payment gateway unavailable if LIST session wasn't created because of incorrect
         * merchant configuration.
         */
        add_action('payoneer-checkout.create_list_session_failed', static function ($arg) use ($container): void {
            if (!$container->get('wp.is_rest_api_request') && !$container->get('checkout.is_frontend_request')) {
                return;
            }
            if (!is_array($arg)) {
                return;
            }
            $exception = $arg['exception'] ?? null;
            if (!$exception instanceof Exception) {
                return;
            }
            $misconfigurationDetector = $container->get('checkout.misconfiguration_detector');
            assert($misconfigurationDetector instanceof MisconfigurationDetectorInterface);
            $exceptionCausedByMisconfiguration = $misconfigurationDetector->isCausedByMisconfiguration($exception);
            if ($exceptionCausedByMisconfiguration) {
                $isHostedFlow = $container->get('checkout.selected_payment_flow') === 'hosted';
                if (!$isHostedFlow) {
                    add_filter('payoneer-checkout.payment_gateway_is_available', '__return_false');
                }
                do_action('payoneer-checkout.payment_gateway_misconfiguration_detected');
            }
        });
    }
    protected function isPayoneerOrderPaymentMethod(ContainerInterface $container, WC_Order $order): bool
    {
        $payoneerGatewayIds = (array) $container->get('payment_gateways');
        return in_array($order->get_payment_method(), $payoneerGatewayIds, \true);
    }
}
