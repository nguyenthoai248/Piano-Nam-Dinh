<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Automattic\WooCommerce\Utilities\OrderUtil;
use Syde\Vendor\Dhii\Collection\MutableContainerInterface;
use Syde\Vendor\Dhii\Services\Factories\Alias;
use Syde\Vendor\Dhii\Services\Factories\Constructor;
use Syde\Vendor\Dhii\Services\Factories\StringService;
use Syde\Vendor\Dhii\Services\Factories\Value;
use Syde\Vendor\Dhii\Services\Factory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\RequestHeaderUtil;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Core\Exception\PayoneerException;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Environment\WpEnvironmentInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\AdminNotice\AdminNoticeEndpointController;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\AdminNotice\AdminNoticeRenderer;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\AdminNotice\AdminNoticeRestEndpoint;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\NormalizingLocaleProviderISO639ISO3166;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\LocaleProviderInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\OrderAdmin\OrderDetailsPage;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\OrderFinder\AddTransactionIdFieldSupport;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\OrderFinder\HposOrderFinder;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\OrderFinder\OrderFinder;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\OrderFinder\OrderFinderInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\RefundFinder\AddPayoutIdFieldSupport;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\RefundFinder\RefundFinder;
use Syde\Vendor\Psr\Container\ContainerInterface;
use Syde\Vendor\WpOop\Containers\Options\BlogOptions;
use Syde\Vendor\WpOop\Containers\Options\SiteMeta;
return static function (): array {
    return [
        'wp.random_seed' => new Factory([], static function (): string {
            return \uniqid('', \true);
        }),
        'wp.current_locale.wp' => new Factory([], static function (): string {
            return \determine_locale();
        }),
        'wp.site.title' => new Factory([], static function (): string {
            return \get_bloginfo('name', 'display');
        }),
        'wp.current_locale.fallback' => new Value(''),
        'wp.current_locale.provider' => new Factory(['wp.current_locale.wp', 'wp.current_locale.fallback'], static function (string $internalLocale, string $defaultLocale): LocaleProviderInterface {
            return new NormalizingLocaleProviderISO639ISO3166($internalLocale, $defaultLocale);
        }),
        'wp.current_locale.normalized' => new Factory(['wp.current_locale.provider'], static function (LocaleProviderInterface $localeProvider): string {
            return $localeProvider->provideLocale();
        }),
        'wp.site_options.not_found_token' => new Alias('wp.random_seed'),
        'wp.sites.current.id' => new Factory([], static function (): ?int {
            return \is_multisite() ? \get_current_blog_id() : null;
        }),
        'wp.sites.current.options' => new Factory(['wp.sites.current.id', 'wp.site_options.not_found_token'], static function (?int $siteId, string $defaultToken): MutableContainerInterface {
            $product = new BlogOptions($siteId, $defaultToken);
            return $product;
        }),
        'wp.sites.current.meta' => new Factory(['wp.sites.current.id'], static function (?int $siteId): MutableContainerInterface {
            $product = new SiteMeta($siteId);
            return $product;
        }),
        'wp.http.wp_http_object' => new Factory([], static function (): \WP_Http {
            return \_wp_http_get_object();
        }),
        'wp.admin_url' => new Factory([], static function (): string {
            return \admin_url();
        }),
        'wp.is_admin' => new Factory([], static function (): bool {
            return \is_admin();
        }),
        'wp.is_ajax' => new Factory([], static function (): bool {
            return \defined('DOING_AJAX') && \DOING_AJAX;
        }),
        'wp.is_frontend_request' => new Factory(['wc'], static function (\WooCommerce $wooCommerce): bool {
            return (!\is_admin() || \defined('DOING_AJAX')) && !\defined('DOING_CRON') && !\defined('REST_REQUEST') && !$wooCommerce->is_rest_api_request();
        }),
        'wp.is_rest_api_request' => new Factory(['wc'], static function (\WooCommerce $wooCommerce): bool {
            return $wooCommerce->is_rest_api_request();
        }),
        'wp.site_url' => new Factory([], static function (): string {
            return \get_site_url(\get_current_blog_id());
        }),
        'wp.is_debug' => new Value(\defined('WP_DEBUG') && \WP_DEBUG),
        'wp.is_script_debug' => new Value(\defined('SCRIPT_DEBUG') && \SCRIPT_DEBUG),
        'wp.user_id' => new Factory([], static function (): string {
            return (string) \get_current_user_id();
        }),
        'wc' => new Factory([], static function (): \WooCommerce {
            if (!\did_action('woocommerce_init')) {
                throw new \RuntimeException('"wc" service was accessed before the "woocommerce_init" hook');
            }
            return \WC();
        }),
        'wc.version' => new Factory(['core.wp_environment'], static function (WpEnvironmentInterface $wpEnvironment): string {
            return $wpEnvironment->getWcVersion();
        }),
        'wc.session' => new Factory(['wc', 'wc.session.is-available'], static function (\WooCommerce $wooCommerce, bool $isAvailable): \WC_Session {
            if (!$isAvailable) {
                throw new PayoneerException('WooCommerce session is not available.');
            }
            return $wooCommerce->session;
        }),
        'wc.customer' => new Factory(['wc'], static function (\WooCommerce $wooCommerce): \WC_Customer {
            return $wooCommerce->customer;
        }),
        'wc.cart' => new Factory(['wc'], static function (\WooCommerce $wooCommerce): \WC_Cart {
            return $wooCommerce->cart;
        }),
        'wc.currency' => new Factory(['wc'], static function (): string {
            return get_woocommerce_currency();
        }),
        'wc.price_decimals' => new Factory(['wc'], static function (): int {
            return \wc_get_price_decimals();
        }),
        'wc.settings.price_include_tax' => new Factory(['wc'], static function (): bool {
            return \wc_prices_include_tax();
        }),
        'wc.is_fragment_update' => new Factory([], static function (): bool {
            $wcAjaxAction = \filter_input(\INPUT_GET, 'wc-ajax', \FILTER_CALLBACK, ['options' => 'sanitize_text_field']);
            return $wcAjaxAction === 'update_order_review' || $wcAjaxAction === 'update_checkout';
        }),
        'wc.is_store_api_request' => new Factory(['wc'], static function (\WooCommerce $wooCommerce): bool {
            /**
             * We really really wish to access raw data here.
             * Wea re also doing only string comparisons and will not use the data
             * for processing. Hence:
             * phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
             * phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
             */
            global $wp_rewrite;
            \assert($wp_rewrite instanceof \WP_Rewrite);
            if ($wp_rewrite->using_permalinks()) {
                /**
                 * is_store_api_request is not available <=8.9.1.
                 * However, block checkout as a whole has been around far longer.
                 * So for older WC versions, we execute a copy of the method we have today
                 */
                if (!\method_exists($wooCommerce, 'is_store_api_request')) {
                    if (empty($_SERVER['REQUEST_URI'])) {
                        return \false;
                    }
                    // phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                    return \false !== \strpos($_SERVER['REQUEST_URI'], \trailingslashit(\rest_get_url_prefix()) . 'wc/store/');
                }
                return $wooCommerce->is_store_api_request();
            }
            return \preg_match('/\/index\.php\?rest_route=\/wc\/store\//', isset($_SERVER['REQUEST_URI']) ? \urldecode($_SERVER['REQUEST_URI']) : '') === 1;
            /**
             * phpcs:enable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
             * phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
             */
        }),
        'wc.order_awaiting_payment' => new Factory(['wc.session'], static function (\WC_Session $session): int {
            /** @var int|false $orderAwaitingPayment */
            $orderAwaitingPayment = $session->get('order_awaiting_payment');
            return (int) $orderAwaitingPayment;
        }),
        'wc.pay_for_order_id.from_header' => new Factory([], static function (): int {
            /**
             * When using the Store API we currently rely on custom HHT header to pass
             * information about the current checkout attempt.
             *
             * @todo: deal with the code duplication. Currently, we have the same logic
             *      for determining context in ListSessionManager. As we may get
             *      rid of contexts soon, I'm leaving it as is so far.
             */
            $headerUtil = new RequestHeaderUtil();
            $paymentCheckoutHeader = 'x-payoneer-is-payment-checkout';
            $orderKey = $headerUtil->getHeader($paymentCheckoutHeader);
            if (!empty($orderKey)) {
                return (int) \wc_get_order_id_by_order_key($orderKey);
            }
            return 0;
        }),
        'wc.order_item_types_for_product' => new Factory([], static function (): array {
            return ['line_item', 'shipping', 'fee', 'coupon'];
        }),
        'wc.ajax_url' => new Factory(['wc'], static function (\WooCommerce $wooCommerce): string {
            return $wooCommerce->ajax_url();
        }),
        'wc.countries' => new Factory(['wc'], static function (\WooCommerce $wooCommerce): \WC_Countries {
            return $wooCommerce->countries;
        }),
        'wc.shop_url' => new Factory([], static function (): string {
            return (string) \get_permalink(\wc_get_page_id('shop'));
        }),
        'wc.admin_permission' => new Value('manage_woocommerce'),
        'wc.hpos.is_enabled' => static function (): bool {
            if (!\method_exists(OrderUtil::class, 'custom_orders_table_usage_is_enabled')) {
                return \false;
            }
            /**
             * @psalm-var mixed $enabled
             */
            $enabled = OrderUtil::custom_orders_table_usage_is_enabled();
            //WooCommerce return types sometimes incorrect, better to make sure.
            return \is_bool($enabled) ? $enabled : \wc_string_to_bool((string) $enabled);
        },
        'wc.is_block_checkout' => fn() => \function_exists('has_block') && \has_block('woocommerce/checkout'),
        /** Order finder */
        'wp.order_finder' => static function (ContainerInterface $container): OrderFinderInterface {
            $transactionIdFieldName = (string) $container->get('webhooks.order.transaction_id_field_name');
            $hposEnabled = (bool) $container->get('wc.hpos.is_enabled');
            return $hposEnabled ? new HposOrderFinder($transactionIdFieldName) : new OrderFinder($transactionIdFieldName);
        },
        'wp.add_transaction_id_field_support' => new Constructor(AddTransactionIdFieldSupport::class, ['webhooks.order.transaction_id_field_name']),
        'wp.refund_finder' => new Constructor(RefundFinder::class, ['webhooks.order_refund.payout_id_field_name']),
        'wp.add_payout_id_field_support' => new Constructor(AddPayoutIdFieldSupport::class, ['webhooks.order_refund.payout_id_field_name']),
        // Asset loader.
        'wp.path.assets' => new StringService('{0}/payoneer-wp/assets/', ['core.local_modules_directory_name']),
        // Order UI changes.
        'wp.order_admin.details_page' => new Constructor(OrderDetailsPage::class, ['core.main_plugin_file', 'wp.path.assets', 'wp.admin_notice.renderer', 'wc.hpos.is_enabled']),
        // Admin notices.
        'wp.admin_notice.renderer' => new Constructor(AdminNoticeRenderer::class, ['core.main_plugin_file', 'wp.path.assets', 'wp.admin_notice.dismiss_rest_endpoint']),
        'wp.admin_notice.rest_controller' => new Constructor(AdminNoticeEndpointController::class),
        'wp.admin_notice.rest_endpoint.namespace' => new Alias('core.webhooks.namespace'),
        'wp.admin_notice.rest_endpoint.dismiss_route' => new Value('/admin-notice/dismiss'),
        'wp.admin_notice.rest_endpoint.dismiss_capability' => new Value('edit_others_shop_orders'),
        'wp.admin_notice.dismiss_rest_endpoint' => new Constructor(AdminNoticeRestEndpoint::class, ['wp.admin_notice.rest_controller', 'wp.admin_notice.rest_endpoint.namespace', 'wp.admin_notice.rest_endpoint.dismiss_route', 'wp.admin_notice.rest_endpoint.dismiss_capability']),
    ];
};
