<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factories\Constructor;
use Syde\Vendor\Dhii\Services\Factories\ServiceList;
use Syde\Vendor\Dhii\Services\Factory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\ProductTaxCodeProvider\ProductTaxCodeProviderInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\TransactionIdGenerator\TransactionIdGeneratorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\Customer\WcBasedCustomerFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\Customer\WcBasedCustomerFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\ListSession\OrderBasedListCommandFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\ListSession\OrderBasedListSessionFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\ListSession\WcBasedListSessionFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\Product\QuantityNormalizer;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\Product\QuantityNormalizerInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\Product\WcBasedProductFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\Product\WcBasedProductFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\Product\WcCartBasedProductListFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ApiListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionManager;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Middleware\ListCache;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Middleware\UpdatingMiddleware;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Middleware\FetchingMiddleware;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\WcProductSerializer\WcProductSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Style\StyleFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\System\SystemInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\PayoneerIntegrationTypes;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\PayoneerInterface;
use Syde\Vendor\Psr\Container\ContainerInterface;
use Syde\Vendor\Psr\Http\Message\UriInterface;
return static function (): array {
    return [
        'list_session.wc_based_customer_factory' => new Constructor(WcBasedCustomerFactory::class, ['core.customer_factory', 'core.phone_factory', 'core.address_factory', 'core.name_factory', 'core.registration_factory', 'checkout.customer_registration_id_field_name', 'checkout.state_provider', 'list_session.fallback_country']),
        'list_session.order_based_list_command_factory' => new Constructor(OrderBasedListCommandFactory::class, ['checkout.payoneer', 'checkout.transaction_id_generator', 'checkout.wc_order_based_callback_factory', 'checkout.wc_order_based_customer_factory', 'checkout.wc_order_based_payment_factory', 'checkout.style_factory', 'checkout.wc_order_based_products_factory', 'list_session.list_session_system', 'wp.current_locale.normalized', 'list_session.fallback_country', 'checkout.merchant_division']),
        'list_session.order_based_list_session_factory' => new Constructor(OrderBasedListSessionFactory::class, ['list_session.order_based_list_command_factory']),
        'list_session.list_session_factory' => new Factory(['core.payoneer', 'core.callback_factory', 'core.style_factory', 'core.payment_factory', 'list_session.wc_based_customer_factory', 'list_session.wc_cart_based_product_list_factory', 'checkout.notification_url', 'wp.current_locale.normalized', 'wc.currency', 'list_session.list_session_system', 'checkout.transaction_id_generator', 'payoneer_settings.merchant_division', 'list_session.fallback_country'], static function (PayoneerInterface $payoneer, CallbackFactoryInterface $callbackFactory, StyleFactoryInterface $styleFactory, PaymentFactoryInterface $paymentFactory, WcBasedCustomerFactoryInterface $wcBasedCustomerFactory, WcCartBasedProductListFactory $wcCartBasedProductListFactory, UriInterface $notificationUrl, string $checkoutLocale, string $currency, SystemInterface $system, TransactionIdGeneratorInterface $transactionIdGenerator, string $division, string $fallbackCountry): WcBasedListSessionFactory {
            return new WcBasedListSessionFactory($payoneer, $callbackFactory, $paymentFactory, $styleFactory, $wcBasedCustomerFactory, $wcCartBasedProductListFactory, $notificationUrl, $checkoutLocale, $currency, $system, $transactionIdGenerator, $division, $fallbackCountry);
        }),
        'list_session.quantity_normalizer' => new Constructor(QuantityNormalizer::class),
        'list_session.wc_based_product_factory' => static function (ContainerInterface $container): WcBasedProductFactoryInterface {
            /** @var WcProductSerializerInterface $wcProductSerializer */
            $wcProductSerializer = $container->get('core.wc_product_serializer');
            /** @var ProductFactoryInterface $productFactory */
            $productFactory = $container->get('core.product_factory');
            /** @var string $currency */
            $currency = $container->get('core.store_currency');
            /** @var QuantityNormalizerInterface $quantityNormalizer */
            $quantityNormalizer = $container->get('list_session.quantity_normalizer');
            /** @var ProductTaxCodeProviderInterface $taxCodeProvider */
            $taxCodeProvider = $container->get('list_session.product_tax_code_provider');
            return new WcBasedProductFactory($wcProductSerializer, $productFactory, $quantityNormalizer, $currency, $taxCodeProvider);
        },
        'list_session.wc_cart_based_product_list_factory' => new Constructor(WcCartBasedProductListFactory::class, ['list_session.wc_based_product_factory', 'checkout.product_factory', 'checkout.store_currency']),
        'list_session.integration_type' => new Factory(['list_session.selected_payment_flow'], static function (string $selectedPaymentFlow): string {
            return $selectedPaymentFlow === 'hosted' ? PayoneerIntegrationTypes::HOSTED : PayoneerIntegrationTypes::EMBEDDED;
        }),
        'list_session.hosted_version' => static function (): string {
            return 'v5';
        },
        'list_session.creator' => new Constructor(ApiListSessionProvider::class, ['list_session.list_session_factory', 'list_session.order_based_list_session_factory', 'list_session.integration_type', 'list_session.can_try_create_list.callable', 'list_session.hosted_version']),
        'list_session.list_cache' => new Constructor(ListCache::class),
        'list_session.middlewares.fetching' => new Constructor(FetchingMiddleware::class, ['payoneer_sdk.commands.fetch', 'list_session.list_cache', 'list_session.selected_payment_flow']),
        'list_session.middlewares.wc-session-update' => new Constructor(UpdatingMiddleware::class, ['list_session.list_session_factory', 'checkout.checkout_hash_provider', 'checkout.session_hash_key', 'core.order_based_update_command_factory', 'wp.is_rest_api_request', 'list_session.list_cache']),
        'list_session.middlewares' => new ServiceList(['list_session.middlewares.wc-session-update', 'list_session.middlewares.fetching', 'list_session.creator']),
        'list_session.manager' => new Constructor(ListSessionManager::class, ['list_session.middlewares']),
        /**
         * Callback returning can_try_create_list value.
         *
         * Since this value may change during the request processing (note this is factories,
         * not services), we want to have the actual value in the class, not the one saved
         * as a class property when it was created.
         */
        'list_session.can_try_create_list.callable' => static fn(ContainerInterface $container) => static fn() => $container->get('list_session.can_try_create_list'),
    ];
};
