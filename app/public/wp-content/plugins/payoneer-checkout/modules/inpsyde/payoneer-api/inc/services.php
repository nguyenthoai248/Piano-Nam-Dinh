<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factories\Constructor;
use Syde\Vendor\Dhii\Services\Factory;
use Syde\Vendor\Dhii\Validator\CallbackValidator;
use Syde\Vendor\Inpsyde\PaymentGateway\NoopPaymentRequestValidator;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Api\BasicTokenProviderFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Api\BasicTokenProviderFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Api\PayoneerFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Client\ClientFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\CommandFactory\WcOrderBasedUpdateCommandFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\Factory\Callback\WcOrderBasedCallbackFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\Factory\Customer\WcOrderBasedCustomerFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\Factory\Payment\WcOrderBasedPaymentFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\Factory\Payment\WcOrderBasedPaymentFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\Factory\Product\FeeItemBasedProductFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\Factory\Product\ProductItemBasedProductFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\Factory\Product\ShippingItemBasedProductFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\Factory\Product\WcOrderBasedProductsFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\Factory\SecurityHeader\SecurityHeaderFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\WcProductSerializer\WcProductSerializer;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\CommandExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Style\StyleInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\System\System;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\System\SystemInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\PayoneerIntegrationTypes;
use Syde\Vendor\Psr\Container\ContainerInterface;
use Syde\Vendor\Psr\Http\Message\UriFactoryInterface;
return static function (): array {
    return ['inpsyde_payoneer_api.basic_token_provider.factory' => new Constructor(BasicTokenProviderFactory::class, []), 'inpsyde_payoneer_api.api_credentials_validator_callback' => static function (ContainerInterface $container): callable {
        return static function (array $credentials) use ($container) {
            $clientFactory = $container->get('inpsyde_payment_gateway.payoneer.client.factory');
            \assert($clientFactory instanceof ClientFactoryInterface);
            $payoneerFactory = $container->get('inpsyde_payment_gateway.payoneer.factory');
            \assert($payoneerFactory instanceof PayoneerFactoryInterface);
            $uriFactory = $container->get('inpsyde_payment_gateway.uri_factory');
            \assert($uriFactory instanceof UriFactoryInterface);
            $tokenProviderFactory = $container->get('inpsyde_payoneer_api.basic_token_provider.factory');
            \assert($tokenProviderFactory instanceof BasicTokenProviderFactoryInterface);
            $dummyCallback = $container->get('inpsyde_payment_gateway.dummy_callback');
            \assert($dummyCallback instanceof CallbackInterface);
            $dummyCustomer = $container->get('inpsyde_payment_gateway.dummy_customer');
            \assert($dummyCustomer instanceof CustomerInterface);
            $dummyPayment = $container->get('inpsyde_payment_gateway.dummy_payment');
            \assert($dummyPayment instanceof PaymentInterface);
            $dummyProduct = $container->get('inpsyde_payment_gateway.dummy_product');
            \assert($dummyProduct instanceof ProductInterface);
            $dummyStyle = $container->get('inpsyde_payment_gateway.dummy_style');
            \assert($dummyStyle instanceof StyleInterface);
            $system = $container->get('inpsyde_payoneer_api.system');
            \assert($system instanceof SystemInterface);
            $storeCountry = 'US';
            $client = $clientFactory->createClientForApi($uriFactory->createUri($credentials['url']), $tokenProviderFactory->createBasicProvider($credentials['code'], $credentials['token']));
            $payoneer = $payoneerFactory->createPayoneerForApi($client);
            $transactionId = \sprintf('tr-%1$d-credentials-test', \time());
            $division = !empty($credentials['division']) ? (string) $credentials['division'] : '';
            $createListCommand = $payoneer->getListCommand()->withApiClient($client)->withTransactionId($transactionId)->withCountry($storeCountry)->withCallback($dummyCallback)->withCustomer($dummyCustomer)->withPayment($dummyPayment)->withProducts([$dummyProduct])->withStyle($dummyStyle)->withOperationType('PRESET')->withSystem($system)->withIntegrationType(PayoneerIntegrationTypes::SELECTIVE_NATIVE)->withDivision($division);
            try {
                $createListCommand->execute();
            } catch (CommandExceptionInterface $exception) {
                return 'Failed to create LIST session. Credentials should be considered invalid.';
            }
            return null;
        };
    }, 'inpsyde_payoneer_api.api_credentials_validator' => new Constructor(CallbackValidator::class, ['inpsyde_payoneer_api.api_credentials_validator_callback']), 'inpsyde_payoneer_api.update_command_factory' => new Constructor(WcOrderBasedUpdateCommandFactory::class, ['inpsyde_payment_gateway.update_command', 'inpsyde_payoneer_api.wc_order_based_payment_factory', 'inpsyde_payoneer_api.wc_order_based_callback_factory', 'inpsyde_payoneer_api.wc_order_based_customer_factory', 'inpsyde_payoneer_api.wc_order_based_products_factory', 'inpsyde_payoneer_api.system', 'core.fallback_country']), 'inpsyde_payoneer_api.wc_order_based_callback_factory' => new Constructor(WcOrderBasedCallbackFactory::class, ['inpsyde_payment_gateway.callback_factory', 'inpsyde_payment_gateway.notification_url', 'inpsyde_payoneer_api.security_header_factory', 'checkout.order.security_header_field_name']), 'inpsyde_payoneer_api.wc_order_based_customer_factory' => new Constructor(WcOrderBasedCustomerFactory::class, ['inpsyde_payment_gateway.customer_factory', 'inpsyde_payment_gateway.phone_factory', 'inpsyde_payment_gateway.address_factory', 'inpsyde_payment_gateway.name_factory', 'inpsyde_payment_gateway.registration_factory', 'inpsyde_payment_gateway.customer_registration_id_field_name', 'inpsyde_payment_gateway.state_provider']), 'inpsyde_payoneer_api.wc_order_based_payment_factory' => new Factory(['inpsyde_payment_gateway.payment_factory', 'inpsyde_payment_gateway.site.title'], static function (PaymentFactoryInterface $paymentFactory, string $siteTitle): WcOrderBasedPaymentFactoryInterface {
        return new WcOrderBasedPaymentFactory($paymentFactory, $siteTitle);
    }), 'inpsyde_payoneer_api.fee_item_based_product_factory' => new Constructor(FeeItemBasedProductFactory::class, ['inpsyde_payment_gateway.product_factory', 'inpsyde_payment_gateway.quantity_normalizer']), 'inpsyde_payoneer_api.product_item_based_product_factory' => new Constructor(ProductItemBasedProductFactory::class, ['inpsyde_payment_gateway.product_factory', 'inpsyde_payment_gateway.quantity_normalizer', 'inpsyde_payment_gateway.price_decimals', 'inpsyde_payment_gateway.product_tax_code_provider']), 'inpsyde_payoneer_api.shipping_item_based_product_factory' => new Constructor(ShippingItemBasedProductFactory::class, ['inpsyde_payment_gateway.product_factory', 'inpsyde_payment_gateway.quantity_normalizer']), 'inpsyde_payoneer_api.wc_order_based_products_factory' => new Constructor(WcOrderBasedProductsFactory::class, ['inpsyde_payoneer_api.product_item_based_product_factory', 'inpsyde_payoneer_api.shipping_item_based_product_factory', 'inpsyde_payoneer_api.fee_item_based_product_factory', 'inpsyde_payment_gateway.order_item_types_for_product']), 'inpsyde_payoneer_api.security_header_factory' => new Constructor(SecurityHeaderFactory::class, ['inpsyde_payment_gateway.header_factory', 'inpsyde_payment_gateway.webhooks.security_header_name']), 'inpsyde_payoneer_api.wc_product_serializer' => new Constructor(WcProductSerializer::class), 'inpsyde_payoneer_api.payment_request_validator' => static fn() => new NoopPaymentRequestValidator(), 'inpsyde_payoneer_api.system' => new Factory(['inpsyde_payment_gateway.plugin.version_string'], static function (string $version): SystemInterface {
        return new System('SHOP_PLATFORM', 'WOOCOMMERCE', $version);
    })];
};
