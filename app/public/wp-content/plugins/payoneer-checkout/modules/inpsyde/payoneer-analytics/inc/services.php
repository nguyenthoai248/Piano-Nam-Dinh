<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factories\Alias;
use Syde\Vendor\Dhii\Services\Factories\Constructor;
use Syde\Vendor\Dhii\Services\Factories\StringService;
use Syde\Vendor\Dhii\Services\Factories\Value;
use Syde\Vendor\Dhii\Services\Factory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Analytics\AnalyticsApiClient\AnalyticsApiClient;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Analytics\AnalyticsEventHandler;
use Syde\Vendor\Inpsyde\Wp\HttpClient\Client;
use Syde\Vendor\Psr\Container\ContainerInterface;
use Syde\Vendor\Psr\Http\Message\UriInterface;
return static function (): array {
    return [
        'analytics.client_id' => new Alias('analytics.wc_shop_url'),
        'analytics.events.plugin_activated' => new Value(["client_id" => "{CLIENT_ID}", "user_id" => "{USER_ID}", "non_personalized_ads" => \true, "events" => [["name" => "plugin_installed", "params" => ["session_id" => "{GA_SESSION_ID}", "store_platform" => "WooCommerce", "engagement_time_msec" => 1, "plugin_name" => "payoneer-checkout", "plugin_version" => "{PLUGIN_VERSION}", "page_location" => "{ADMIN_URL}"]]]]),
        'analytics.events.checkout_page_viewed' => new Value(["client_id" => "{CLIENT_ID}", "user_id" => "{USER_ID}", "non_personalized_ads" => \true, "events" => [["name" => "begin_checkout", "params" => ["currency" => "{CURRENCY}", "value" => "{TOTAL_AMOUNT}", "session_id" => "{GA_SESSION_ID}", "engagement_time_msec" => 1, "page_location" => "{PAGE_LOCATION}", "payment_type" => "{PAYMENT_METHOD_ID}", "store_code" => "{STORE_CODE}"]]]]),
        'analytics.events.checkout_form_submitted.order_created' => new Value(["client_id" => "{CLIENT_ID}", "user_id" => "{USER_ID}", "user_properties" => ["country" => ["value" => "{BILLING_COUNTRY}"]], "non_personalized_ads" => \true, "events" => [["name" => "checkout_attempt", "params" => ["checkout_status" => "Successful", "session_id" => "{GA_SESSION_ID}", "engagement_time_msec" => 1, "payment_type" => "{PAYMENT_METHOD_ID}", "currency" => "{CURRENCY}", "value" => "{TOTAL_AMOUNT}", "page_location" => "{PAGE_LOCATION}", "store_code" => "{STORE_CODE}"]], ["name" => "add_payment_info", "params" => ["currency" => "{CURRENCY}", "value" => "{TOTAL_AMOUNT}", "payment_type" => "{PAYMENT_METHOD_ID}", "session_id" => "{GA_SESSION_ID}", "engagement_time_msec" => 1, "page_location" => "{PAGE_LOCATION}", "store_code" => "{STORE_CODE}"]]]]),
        'analytics.events.checkout_form_submitted.order_failed' => new Value(["client_id" => "{CLIENT_ID}", "user_id" => "{USER_ID}", "user_properties" => ["country" => ["value" => "{BILLING_COUNTRY}"]], "non_personalized_ads" => \true, "events" => [["name" => "checkout_attempt", "params" => ["checkout_status" => "Failed", "session_id" => "{GA_SESSION_ID}", "engagement_time_msec" => 1, "payment_type" => "{PAYMENT_METHOD_ID}", "currency" => "{CURRENCY}", "value" => "{TOTAL_AMOUNT}", "page_location" => "{PAGE_LOCATION}", "store_code" => "{STORE_CODE}"]]]]),
        'analytics.events.order_pay_form_submitted' => new Value(["client_id" => "{CLIENT_ID}", "user_id" => "{USER_ID}", "user_properties" => ["country" => ["value" => "{BILLING_COUNTRY}"]], "non_personalized_ads" => \true, "events" => [["name" => "add_payment_info", "params" => ["currency" => "{CURRENCY}", "value" => "{TOTAL_AMOUNT}", "payment_type" => "{PAYMENT_METHOD_ID}", "session_id" => "{GA_SESSION_ID}", "engagement_time_msec" => 1, "page_location" => "{PAGE_LOCATION}", "store_code" => "{STORE_CODE}"]]]]),
        'analytics.events.order_status_changed_to_processing' => new Value(['client_id' => "{CLIENT_ID}", 'user_id' => "{USER_ID}", 'user_properties' => ['country' => ['value' => "{BILLING_COUNTRY}"]], 'non_personalized_ads' => \true, 'events' => [["name" => "order_status_updated", "params" => ["order_status" => "Successful", "session_id" => "{GA_SESSION_ID}", "engagement_time_msec" => 1, "payment_type" => "{PAYMENT_METHOD_ID}", "currency" => "{CURRENCY}", "value" => "{TOTAL_AMOUNT}", "page_location" => "{PAGE_LOCATION}", "store_code" => "{STORE_CODE}"]], ["name" => "purchase", "params" => ["currency" => "{CURRENCY}", "value" => "{TOTAL_AMOUNT}", "session_id" => "{GA_SESSION_ID}", "engagement_time_msec" => 1, "payment_type" => "{PAYMENT_METHOD_ID}", "page_location" => "{PAGE_LOCATION}", "store_code" => "{STORE_CODE}"]]]]),
        'analytics.events.order_status_changed_to_failed' => new Value(["client_id" => "{CLIENT_ID}", "user_id" => "{USER_ID}", "user_properties" => ["country" => ["value" => "{BILLING_COUNTRY}"]], "non_personalized_ads" => \true, "events" => [["name" => "order_status_updated", "params" => ["order_status" => "Failed", "session_id" => "{GA_SESSION_ID}", "engagement_time_msec" => 1, "payment_type" => "{PAYMENT_METHOD_ID}", "currency" => "{CURRENCY}", "value" => "{TOTAL_AMOUNT}", "page_location" => "{PAGE_LOCATION}", "store_code" => "{STORE_CODE}"]]]]),
        'analytics.analytics_events' => new Factory(['analytics.events.plugin_activated', 'analytics.events.checkout_page_viewed', 'analytics.events.checkout_form_submitted.order_created', 'analytics.events.checkout_form_submitted.order_failed', 'analytics.events.order_pay_form_submitted', 'analytics.events.order_status_changed_to_processing', 'analytics.events.order_status_changed_to_failed'], static function (array $pluginActivatedEventConfig, array $checkoutPageViewedConfig, array $orderCreatedFromCheckoutConfig, array $orderFailedFromCheckoutConfig, array $orderPayFormSubmittedConfig, array $orderStatusChangedToProcessing, array $orderStatusChangedToFailedConfig): array {
            return ['payoneer-checkout_plugin_activated' => $pluginActivatedEventConfig, 'payoneer-checkout.checkout_page_viewed' => $checkoutPageViewedConfig, 'payoneer-checkout.order_created_from_checkout' => $orderCreatedFromCheckoutConfig, 'payoneer-checkout.order_failed_from_checkout' => $orderFailedFromCheckoutConfig, 'payoneer-checkout.pay_for_order_form_submitted' => $orderPayFormSubmittedConfig, 'payoneer-checkout.order_status_changed_to_processing' => $orderStatusChangedToProcessing, 'payoneer-checkout.order_status_changed_to_failed' => $orderStatusChangedToFailedConfig];
        }),
        'analytics.analytics_api_client' => new Constructor(AnalyticsApiClient::class, ['analytics.http.request_target_url', 'analytics.http.request_factory', 'analytics.http.http_client', 'analytics.stream_factory']),
        'analytics.http.request_target_url' => new StringService('https://www.google-analytics.com/mp/collect?measurement_id={0}&api_secret={1}', ['analytics.credentials.measurement_id', 'analytics.credentials.api_secret']),
        'analytics.event_handler' => new Constructor(AnalyticsEventHandler::class, ['analytics.analytics_api_client', 'analytics.base_context']),
        'analytics.base_context' => new Factory(['analytics.client_id', 'analytics.user_id', 'analytics.admin_url', 'analytics.ga_session_id_provider', 'analytics.http.current_url'], static function (string $clientId, string $userId, string $adminUrl, callable $gaSessionIdProvider, UriInterface $pageLocation): array {
            return ['CLIENT_ID' => $clientId, 'USER_ID' => $userId, 'ADMIN_URL' => $adminUrl, 'GA_SESSION_ID' => $gaSessionIdProvider, 'PAGE_LOCATION' => $pageLocation->__toString()];
        }),
        //todo: replace with production value
        'analytics.credentials.measurement_id' => new Value('G-7X39XK9YXE'),
        //todo: replace with production value
        'analytics.credentials.api_secret' => new Value('kRt4iSkNTlS-uboOiZUs1A'),
        'analytics.http.http_client.options' => new Value(['timeout' => 2]),
        'analytics.http.measurement_id' => new Value(''),
        'analytics.http.http_client' => new Constructor(Client::class, ['analytics.http.wp_http_object', 'analytics.http.request_factory', 'analytics.http.response_factory', 'analytics.stream_factory', 'analytics.http.http_client.options']),
        'analytics.wc.cart' => new Alias('wc.cart'),
        'analytics.ga_session_id_provider' => static function (ContainerInterface $container): callable {
            return static function () use ($container): string {
                try {
                    $session = $container->get('analytics.wc.session');
                    if (\is_object($session) && \method_exists($session, 'get_session_cookie')) {
                        $sessionCookie = $session->get_session_cookie();
                        if (\is_array($sessionCookie) && isset($sessionCookie[3])) {
                            //WC session hash
                            return (string) $sessionCookie[3];
                        }
                    }
                    return (string) \time();
                } catch (\Throwable $throwable) {
                    return (string) \time();
                }
            };
        },
        'analytics.ga_session_id_order_field_name' => new Value('payoneer_ga_session_id'),
        'analytics.is_order_received_page' => new Alias('wc.is_order_received_page'),
    ];
};
