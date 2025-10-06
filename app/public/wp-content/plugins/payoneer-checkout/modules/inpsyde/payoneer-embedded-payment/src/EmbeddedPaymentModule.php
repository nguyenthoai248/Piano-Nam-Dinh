<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment;

use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;
use Syde\Vendor\Dhii\Services\Factories\FuncService;
use Syde\Vendor\Inpsyde\Assets\Asset;
use Syde\Vendor\Inpsyde\Assets\AssetManager;
use Syde\Vendor\Inpsyde\Modularity\Module\ExecutableModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ExtendingModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Syde\Vendor\Inpsyde\Modularity\Module\ServiceModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\AjaxOrderPay\AjaxPayAction;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\AjaxOrderPay\OrderPayload;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionManager;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\PaymentContext;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\WebSdk\Security\SdkIntegrityService;
use Syde\Vendor\Psr\Container\ContainerExceptionInterface;
use Syde\Vendor\Psr\Container\ContainerInterface;
use Syde\Vendor\Psr\Container\NotFoundExceptionInterface;
use WC_Data_Exception;
use WC_Order;
/**
 * phpcs:disable Inpsyde.CodeQuality.NestingLevel.High
 * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
 * phpcs:disable WordPress.WP.I18n.TextDomainMismatch
 */
class EmbeddedPaymentModule implements ExecutableModule, ServiceModule, ExtendingModule
{
    use ModuleClassNameIdTrait;
    /**
     * @inheritDoc
     */
    public function run(ContainerInterface $container): bool
    {
        add_action('payoneer-checkout.init_checkout', function () use ($container): void {
            $isEnabled = (bool) $container->get('embedded_payment.is_enabled');
            if (!$isEnabled) {
                return;
            }
            $this->setupModuleActions($container);
        });
        /**
         * Our 'payoneer-checkout.init_checkout' hook fires too late for this, so this must stay
         * out of $this->setupModuleActions() method call.
         */
        $this->registerSendingListDataToFrontend($container);
        $this->registerPaymentDeclinedListener($container);
        return \true;
    }
    /**
     * @param ContainerInterface $container
     *
     * @return void
     * @throws WC_Data_Exception
     */
    protected function setupModuleActions(ContainerInterface $container): void
    {
        $isFrontendRequest = $container->get('wp.is_frontend_request');
        if ($isFrontendRequest) {
            $this->registerAssets($container);
        }
        $this->registerSessionHandling($container);
        $this->registerAjaxOrderPay($container);
    }
    /**
     * Setup module assets registration.
     *
     * @param ContainerInterface $container
     *
     * @return void
     */
    public function registerAssets(ContainerInterface $container): void
    {
        add_action(AssetManager::ACTION_SETUP, static function (AssetManager $assetManager) use ($container) {
            /**
             * Although the same will be checked by Asset Manager later,
             * by checking it here we can prevent pulling lots of other services
             * we don't need at the moment. Some of them, like List provider, cannot be used
             * properly in many cases.
             */
            $canEnqueue = $container->get('embedded_payment.assets.can_enqueue');
            assert(is_callable($canEnqueue));
            if (!$canEnqueue()) {
                return;
            }
            /** @var Asset[] $assets */
            $assets = $container->get('embedded_payment.assets');
            $assetManager->register(...$assets);
        });
    }
    /**
     * Recover from catastrophic failure during the payment process
     *
     * @param WC_Order $order
     * @param ListSessionManager $listSessionManager
     * @param string $onBeforeServerErrorFlag
     *
     * @return void
     */
    public function beforeOrderPay(WC_Order $order, ListSessionManager $listSessionManager, string $onBeforeServerErrorFlag): void
    {
        $interactionCode = filter_input(\INPUT_GET, 'interactionCode', \FILTER_CALLBACK, ['options' => 'sanitize_text_field']);
        $onBeforeServerError = filter_input(\INPUT_GET, $onBeforeServerErrorFlag, \FILTER_CALLBACK, ['options' => 'sanitize_text_field']);
        if ($onBeforeServerError) {
            /**
             * Safely redirect without the $onBeforeServerError flag.
             */
            wp_safe_redirect($order->get_checkout_payment_url());
            exit;
        }
        if (!$interactionCode || $order->is_paid()) {
            return;
        }
        if (!in_array($interactionCode, ['RETRY', 'ABORT'], \true)) {
            return;
        }
        /**
         * Since we went here directly from the checkout page (redirect during client-side CHARGE),
         * WooCommerce did not have  the chance to clear the cart/session yet.
         * We'll do this explicitly here,
         * so that visiting the checkout page does not display a stale session
         */
        WC()->cart->empty_cart();
        /**
         * We redirect to the payment URL sans OPG parameters for 3 reasons:
         * 1. It's cleaner
         * 2. The WebSDK appears to pick up the URL parameters and attempt to re-use the aborted session
         * 3. We can make sure we start fresh on a new HTTP request
         */
        wp_safe_redirect($order->get_checkout_payment_url());
        exit;
    }
    /**
     * For embedded flow, we need to create a LIST session ahead of time.
     * Based on customer and Cart data, a LIST object will be serialized into the
     * checkout session and kept updated if relevant data changes
     *
     * @param ContainerInterface $container
     *
     * @return void
     */
    public function registerSessionHandling(ContainerInterface $container): void
    {
        add_action('wp', function () use ($container) {
            if (!$container->get('wc.is_checkout_pay_page')) {
                return;
            }
            $orderId = get_query_var('order-pay');
            $wcOrder = wc_get_order($orderId);
            if (!$wcOrder instanceof WC_Order) {
                return;
            }
            $listSessionManager = $container->get('list_session.manager');
            assert($listSessionManager instanceof ListSessionManager);
            $onBeforeServerErrorFlag = (string) $container->get('embedded_payment.pay_order_error_flag');
            $this->beforeOrderPay($wcOrder, $listSessionManager, $onBeforeServerErrorFlag);
        }, 0);
    }
    protected function registerSendingListDataToFrontend(ContainerInterface $container): void
    {
        add_action('woocommerce_init', function () use ($container) {
            /**
             * We support WooCommerce 5.0.0 - 6.9.0, so versions predating block checkout / store API
             * TODO: Remove this check when support for these versions is dropped
             */
            if (!function_exists('woocommerce_store_api_register_endpoint_data')) {
                return;
            }
            woocommerce_store_api_register_endpoint_data(['endpoint' => CartSchema::IDENTIFIER, 'namespace' => 'payoneer-checkout', 'data_callback' => function () use ($container): array {
                return $this->provideCartExtensionData($container);
            }, 'schema_callback' => fn() => ['longId' => ['description' => 'LongId of the LIST session', 'type' => 'string', 'readonly' => \true], 'environment' => ['description' => 'The current environment', 'type' => 'string', 'readonly' => \true], 'sdkVersion' => ['description' => 'Pinned WebSDK script version', 'type' => 'string', 'readonly' => \true], 'sdkIntegrity' => ['description' => 'WebSDK integrity hash', 'type' => 'string', 'readonly' => \true], 'comment' => ['description' => 'Arbitrary text with debugging info, error description, etc.', 'type' => 'string', 'readonly' => \true]], 'schema_type' => \ARRAY_A]);
        });
    }
    private function provideCartExtensionData(ContainerInterface $container): array
    {
        /**
         * During the initial page load,
         * WooCommerce will pre-warm some API call for usage in blocks.
         * This data is injected into wp.apiFetch via a preloadingMiddleware so it is
         * returned as the result of actual API/HTTP calls.
         * In other words, the first JS call to '/wc/store/v1/cart' (and others)
         * is always pre-warmed in PHP.
         *
         * Since we explicitly want to make this data available lazily and prevent
         * LIST creation when it is not needed - or impossible,
         * we check if we are currently doing an API call
         *
         * @see \Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry::hydrate_api_request
         */
        $isStoreApi = $container->get('wc.is_store_api_request');
        $emptyResponse = ['longId' => null, 'environment' => null, 'sdkVersion' => null, 'sdkIntegrity' => null];
        if (!$isStoreApi) {
            $emptyResponse['comment'] = 'Current request is not Store REST API request';
            return $emptyResponse;
        }
        $listProvider = $container->get('list_session.manager');
        assert($listProvider instanceof ListSessionProvider);
        $envExtractor = $container->get('embedded_payment.list_url_environment_extractor');
        assert($envExtractor instanceof ListUrlEnvironmentExtractor);
        try {
            $list = $listProvider->provide(new PaymentContext());
        } catch (\Throwable $throwable) {
            $emptyResponse['comment'] = 'Cannot get List, throwable caught: ' . $throwable->getMessage();
            return $emptyResponse;
        }
        // TODO: Refactor the environment detection to use the current WP options.
        // Extract the environment name from the LIST response.
        $environment = $envExtractor->extract($list->getLinks()['self'] ?? '');
        // TODO: Determine whether we can get SRI details (URL + hash) from the LIST response, and drop this service.
        $sdkIntegrity = $container->get('websdk.security.integrity');
        assert($sdkIntegrity instanceof SdkIntegrityService);
        $sdkIntegrity->setVersion($environment);
        return [
            'longId' => $list->getIdentification()->getLongId(),
            // TODO: Consider returning the full SDK URL instead of only the environment. We could return the final `websdk.assets.umd.url.template` here.
            'environment' => $environment,
            'sdkVersion' => $sdkIntegrity->getVersion(),
            'sdkIntegrity' => $sdkIntegrity->getHash(),
        ];
    }
    /**
     * Client-side CHARGE requires us to validate & create the order *before* attempting payment.
     * The payment page is a classic form submission, followed by hard redirect & exit handling by
     * WooCommerce. This unfortunately means we cannot just AJAXify that POST request.
     * \WC_Form_Handler does not provide us with any decoupled subset of functionality that we
     * could use. So here, we practically re-implement order-pay as an AJAX call.
     *
     * @see \WC_Form_Handler::pay_action()
     * @param ContainerInterface $container
     *
     * @return void
     */
    private function registerAjaxOrderPay(ContainerInterface $container): void
    {
        $onAjaxOrderPay = function () use ($container) {
            try {
                $delegate = new FuncService(['embedded_payment.ajax_order_pay.checkout_payload', 'embedded_payment.ajax_order_pay.payment_action'], \Closure::fromCallable([$this, 'onAjaxOrderPay']));
                /** @psalm-suppress MixedFunctionCall */
                $delegate($container)();
            } catch (\Throwable $exception) {
                wc_add_notice($exception->getMessage(), 'error');
                wp_send_json_error(['result' => 'failure'], 500);
            }
        };
        add_action('wp_ajax_payoneer_order_pay', $onAjaxOrderPay);
        add_action('wp_ajax_nopriv_payoneer_order_pay', $onAjaxOrderPay);
    }
    /**
     * @param OrderPayload $payload
     * @param AjaxPayAction $payAction
     *
     * @return void
     */
    protected function onAjaxOrderPay(OrderPayload $payload, AjaxPayAction $payAction)
    {
        if ($payAction($payload->getOrder(), $payload->getCustomer(), $payload->getFormData())) {
            wp_send_json_success(['result' => 'success'], 200);
        }
        wp_send_json_error(['result' => 'failure'], 500);
    }
    protected function registerPaymentDeclinedListener(ContainerInterface $container): void
    {
        add_action('wc_ajax_payoneer-checkout-payment-declined', function () use ($container) {
            $nonceAction = (string) $container->get('embedded_payment.nonce.action.on_payment_declined');
            check_ajax_referer($nonceAction);
            try {
                $orderId = $this->getOrderIdForPaymentDeclinedRequest($container);
            } catch (\Throwable $exception) {
                wp_send_json_error('Failed to change order status in payment declined request.');
            }
            $order = wc_get_order($orderId);
            if (!$order instanceof WC_Order) {
                //Typecast $orderId to make psalm happy.
                wp_send_json_error(sprintf('Cannot get order by ID %s', $orderId));
            }
            if (!$this->isSupportedPaymentMethod($order, $container)) {
                wp_send_json_error('Unexpected payment method');
            }
            $order->update_status('failed', 'Setting order failed after payment declined.' . \PHP_EOL);
            $order->save();
            wp_send_json_success(['message' => 'Order status was set to failed.', 'nonce' => wp_create_nonce($nonceAction)]);
        });
    }
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getOrderIdForPaymentDeclinedRequest(ContainerInterface $container): int
    {
        $orderKey = (string) filter_input(\INPUT_POST, 'orderKey', \FILTER_CALLBACK, ['options' => fn($rawInput) => sanitize_text_field((string) wp_unslash($rawInput))]);
        /**
         * We need a way of getting order ID directly from post for the block checkout.
         *
         * Block checkout doesn't keep the order ID under the `order_awaiting_payment` key
         * in WC Session. We still could get the order ID from the `store_api_draft_order` session
         * key, but is not so reliable. Also, this creates problems in potential corner cases when
         * both block and classic checkouts are configured in the store and both session keys have
         * some order IDs.
         *
         * Sending order ID directly from frontend is less secure than getting it from a WC Session
         * on backend, but we are compensating it by comparing longId and verifying nonce.
         */
        $orderId = filter_input(\INPUT_POST, 'payoneerOrderId', \FILTER_SANITIZE_NUMBER_INT);
        if (!$orderId) {
            $orderId = $orderKey ? wc_get_order_id_by_order_key($orderKey) : $container->get('wc.order_under_payment');
        }
        return (int) $orderId;
    }
    protected function isSupportedPaymentMethod(WC_Order $order, ContainerInterface $container): bool
    {
        $payoneerMethods = $container->get('payment_methods.all');
        assert(is_array($payoneerMethods));
        return in_array($order->get_payment_method(), $payoneerMethods, \true);
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
}
