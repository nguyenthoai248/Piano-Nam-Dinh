<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway;

use Syde\Vendor\Dhii\Services\Factories\Alias;
use Syde\Vendor\Dhii\Services\Factories\Constructor;
use Syde\Vendor\Dhii\Services\Factories\FuncService;
use Syde\Vendor\Dhii\Services\Factories\ServiceList;
use Syde\Vendor\Dhii\Services\Factories\StringService;
use Syde\Vendor\Dhii\Services\Factories\Value;
use Syde\Vendor\Dhii\Services\Factory;
use Syde\Vendor\Dhii\Services\Service;
use Syde\Vendor\Dhii\Validator\CallbackValidator;
use Generator;
use Syde\Vendor\Inpsyde\Assets\Asset;
use Syde\Vendor\Inpsyde\Assets\Script;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PageDetector\PageDetectorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Api\BasicTokenProviderFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Api\BasicTokenProviderFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Api\PayoneerFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Client\ClientFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\CommandFactory\WcOrderBasedUpdateCommandFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Config\PaymentGatewayConfig;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\ExcludeNotSupportedCountries;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Callback\WcOrderBasedCallbackFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Customer\WcOrderBasedCustomerFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Payment\WcOrderBasedPaymentFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Payment\WcOrderBasedPaymentFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Product\FeeItemBasedProductFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Product\ProductItemBasedProductFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Product\ShippingItemBasedProductFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Product\WcOrderBasedProductsFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\SecurityHeader\SecurityHeaderFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\PaymentGateway;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\PaymentRequestValidatorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\RefundProcessor\RefundProcessor;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Settings\PlainTextField;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Settings\TokenField;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Settings\VirtualField;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\WcProductSerializer\WcProductSerializer;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant\CheckoutMerchantAwareUrlTemplateProvidingMerchant;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant\ContainerMapMerchantModel;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant\EnvironmentAwareUrlTemplateProvidingMerchant;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant\MerchantFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant\MerchantFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant\MerchantInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant\MerchantQueryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant\MerchantSerializer;
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
use Syde\Vendor\Psr\Container\NotFoundExceptionInterface;
use Syde\Vendor\Psr\Http\Message\UriFactoryInterface;
use Syde\Vendor\Psr\Http\Message\UriInterface;
return static function () : array {
    $moduleRoot = dirname(__FILE__, 2);
    return [
        'inpsyde_payment_gateway.module_dir' => new Value($moduleRoot),
        'inpsyde_payment_gateway.module_name' => new Value('payoneer-payment-gateway'),
        'inpsyde_payment_gateway.gateway' => static function (ContainerInterface $container) {
            /** @psalm-suppress MixedFunctionCall **/
            return (new FuncService(['inpsyde_payment_gateway.config', 'inpsyde_payment_gateway.settings_fields', 'inpsyde_payment_gateway.options', 'inpsyde_payment_gateway.settings_option_key', 'inpsyde_payment_gateway.payout_id_field_name', 'inpsyde_payment_gateway.merchant', 'inpsyde_payment_gateway.merchant.query', 'inpsyde_payment_gateway.merchant.cmd.save', 'inpsyde_payment_gateway.merchant.deserializer'], static function () : PaymentGateway {
                /** @psalm-suppress MixedArgument **/
                return new PaymentGateway(...func_get_args());
            }))($container)($container);
        },
        'inpsyde_payment_gateway.gateway.id' => new Value('payoneer-checkout'),
        'inpsyde_payment_gateway.gateway.can_be_used' => new Factory(['inpsyde_payment_gateway.is_live_mode', 'inpsyde_payment_gateway.show_payment_widget_to_customers_in_sandbox_mode'], static function (bool $isLiveMode, bool $renderWidgetInSandboxMode) : callable {
            return static function () use($isLiveMode, $renderWidgetInSandboxMode) : bool {
                return $isLiveMode || current_user_can('manage_woocommerce') || $renderWidgetInSandboxMode;
            };
        }),
        'inpsyde_payment_gateway.is_enabled' => new Factory(['inpsyde_payment_gateway.options'], static function (ContainerInterface $options) : bool {
            /** @psalm-suppress InvalidCatch */
            try {
                return $options->get('enabled') === 'yes';
            } catch (NotFoundExceptionInterface $exc) {
                return \false;
                // default
            }
        }),
        'inpsyde_payment_gateway.config.description' => new Factory(['inpsyde_payment_gateway.is_settings_page', 'inpsyde_payment_gateway.config.description.settings_page', 'inpsyde_payment_gateway.config.description.payment_methods_page'], static function (bool $isSettingsPage, string $settingsPageDescription, string $paymentMethodsPageDescription) : string {
            return $isSettingsPage ? $settingsPageDescription : $paymentMethodsPageDescription;
        }),
        'inpsyde_payment_gateway.config.description.payment_methods_page' => new Factory([], static function () : string {
            $description = __('Payoneer Checkout is the next generation of payment processing platforms.', 'payoneer-checkout');
            $descriptionLegal = sprintf(
                /* translators: %1$s, %2$s, %3$s and %4$s are replaced with opening and closing 'a' tags. */
                __('By using Payoneer Checkout, you agree to the %1$sTerms of Service%2$s and %3$sPrivacy policy%4$s.', 'payoneer-checkout'),
                '<a href="https://www.payoneer.com/legal-agreements/?cnty=HK" target="_blank">',
                '</a>',
                '<a target="_blank" href="https://www.payoneer.com/legal/privacy-policy/">',
                '</a>'
            );
            return sprintf('<p>%1$s</p><p>%2$s</p>', $description, $descriptionLegal);
        }),
        'inpsyde_payment_gateway.config.description.settings_page' => new Factory([], static function () : string {
            return sprintf(
                /* translators: %1$s, %2$s, %3$s, %4$s, %5$s and %6$s is replaced with the opening and closing 'a' tags.*/
                __('Before you begin read How to %1$sConnect WooCommerce%2$s to Payoneer Checkout. Make sure you have a Payoneer Account. If you don\'t, see %3$sRegister for Checkout%4$s. You can get your %5$sauthentication data%6$s in the Payoneer Account.', 'payoneer-checkout'),
                '<a href="https://checkoutdocs.payoneer.com/docs/integrate-with-woocommerce" target="_blank">',
                '</a>',
                '<a href="https://www.payoneer.com/solutions/checkout/woocommerce-integration/?utm_source=Woo+plugin&utm_medium=referral&utm_campaign=WooCommerce+config+page#form-modal-trigger" target="_blank">',
                '</a>',
                '<a href="https://myaccount.payoneer.com/ma/checkout/tokens" target="_blank">',
                '</a>'
            );
        }),
        'inpsyde_payment_gateway.config' => new Factory(['inpsyde_payment_gateway.gateway.id', 'inpsyde_payment_gateway.config.description'], static function (string $id, string $description) : ContainerInterface {
            return new PaymentGatewayConfig([
                'id' => $id,
                'method_title' => __('Payoneer Checkout', 'payoneer-checkout'),
                'method_description' => $description,
                'order_button_text' => '',
                'countries' => [],
                'icon' => '',
                'view_transaction_url' => '',
                'pay_button_id' => 'payoneer-checkout-pay-button',
                'tokens' => [],
                'supports' => ['products', 'refunds'],
                /* translators: %1$s is replaced with the refund long ID */
                'refund_reason_suffix_template' => __(' Refunded by Payoneer Checkout - long ID: %1$s', 'payoneer-checkout'),
                'max_amount' => 0,
            ]);
        }),
        'inpsyde_payment_gateway.settings_fields' => Service::fromFile("{$moduleRoot}/inc/fields.php"),
        'inpsyde_payment_gateway.update_command_factory' => new Constructor(WcOrderBasedUpdateCommandFactory::class, ['inpsyde_payment_gateway.update_command', 'inpsyde_payment_gateway.wc_order_based_payment_factory', 'inpsyde_payment_gateway.wc_order_based_callback_factory', 'inpsyde_payment_gateway.wc_order_based_customer_factory', 'inpsyde_payment_gateway.wc_order_based_products_factory', 'inpsyde_payment_gateway.system']),
        'inpsyde_payment_gateway.wc_order_based_callback_factory' => new Constructor(WcOrderBasedCallbackFactory::class, ['inpsyde_payment_gateway.callback_factory', 'inpsyde_payment_gateway.notification_url', 'inpsyde_payment_gateway.security_header_factory', 'checkout.order.security_header_field_name']),
        'inpsyde_payment_gateway.wc_order_based_customer_factory' => new Constructor(WcOrderBasedCustomerFactory::class, ['inpsyde_payment_gateway.customer_factory', 'inpsyde_payment_gateway.phone_factory', 'inpsyde_payment_gateway.address_factory', 'inpsyde_payment_gateway.name_factory', 'inpsyde_payment_gateway.registration_factory', 'inpsyde_payment_gateway.customer_registration_id_field_name', 'inpsyde_payment_gateway.state_provider']),
        'inpsyde_payment_gateway.wc_order_based_payment_factory' => new Factory(['inpsyde_payment_gateway.payment_factory', 'inpsyde_payment_gateway.site.title'], static function (PaymentFactoryInterface $paymentFactory, string $siteTitle) : WcOrderBasedPaymentFactoryInterface {
            return new WcOrderBasedPaymentFactory($paymentFactory, $siteTitle);
        }),
        'inpsyde_payment_gateway.wc_order_based_products_factory' => new Constructor(WcOrderBasedProductsFactory::class, ['inpsyde_payment_gateway.product_item_based_product_factory', 'inpsyde_payment_gateway.shipping_item_based_product_factory', 'inpsyde_payment_gateway.fee_item_based_product_factory', 'inpsyde_payment_gateway.order_item_types_for_product']),
        'inpsyde_payment_gateway.security_header_factory' => new Constructor(SecurityHeaderFactory::class, ['inpsyde_payment_gateway.header_factory', 'inpsyde_payment_gateway.webhooks.security_header_name']),
        'inpsyde_payment_gateway.product_item_based_product_factory' => new Constructor(ProductItemBasedProductFactory::class, ['inpsyde_payment_gateway.product_factory', 'inpsyde_payment_gateway.quantity_normalizer', 'inpsyde_payment_gateway.price_decimals', 'inpsyde_payment_gateway.product_tax_code_provider']),
        'inpsyde_payment_gateway.shipping_item_based_product_factory' => new Constructor(ShippingItemBasedProductFactory::class, ['inpsyde_payment_gateway.product_factory', 'inpsyde_payment_gateway.quantity_normalizer']),
        'inpsyde_payment_gateway.fee_item_based_product_factory' => new Constructor(FeeItemBasedProductFactory::class, ['inpsyde_payment_gateway.product_factory', 'inpsyde_payment_gateway.quantity_normalizer']),
        'inpsyde_payment_gateway.refund_processor' => new Constructor(RefundProcessor::class, ['inpsyde_payment_gateway.payoneer', 'list_session.manager', 'inpsyde_payment_gateway.payment_factory', 'inpsyde_payment_gateway.charge_id_field_name']),
        'inpsyde_payment_gateway.payment_request_validator' => static function () : PaymentRequestValidatorInterface {
            return new class implements PaymentRequestValidatorInterface
            {
                public function assertIsValid(\WC_Order $wcOrder, PaymentGateway $gateway) : void
                {
                }
            };
        },
        'inpsyde_payment_gateway.wc_product_serializer' => new Constructor(WcProductSerializer::class),
        'inpsyde_payment_gateway.basic_token_provider.factory' => new Constructor(BasicTokenProviderFactory::class, []),
        'inpsyde_payment_gateway.system' => new Factory(['inpsyde_payment_gateway.plugin.version_string'], static function (string $version) : SystemInterface {
            return new System('SHOP_PLATFORM', 'WOOCOMMERCE', $version);
        }),
        'inpsyde_payment_gateway.api_credentials_validator_callback' => static function (ContainerInterface $container) : callable {
            return static function (array $credentials) use($container) {
                $clientFactory = $container->get('inpsyde_payment_gateway.payoneer.client.factory');
                assert($clientFactory instanceof ClientFactoryInterface);
                $payoneerFactory = $container->get('inpsyde_payment_gateway.payoneer.factory');
                assert($payoneerFactory instanceof PayoneerFactoryInterface);
                $uriFactory = $container->get('inpsyde_payment_gateway.uri_factory');
                assert($uriFactory instanceof UriFactoryInterface);
                $tokenProviderFactory = $container->get('inpsyde_payment_gateway.basic_token_provider.factory');
                assert($tokenProviderFactory instanceof BasicTokenProviderFactoryInterface);
                $dummyCallback = $container->get('inpsyde_payment_gateway.dummy_callback');
                assert($dummyCallback instanceof CallbackInterface);
                $dummyCustomer = $container->get('inpsyde_payment_gateway.dummy_customer');
                assert($dummyCustomer instanceof CustomerInterface);
                $dummyPayment = $container->get('inpsyde_payment_gateway.dummy_payment');
                assert($dummyPayment instanceof PaymentInterface);
                $dummyProduct = $container->get('inpsyde_payment_gateway.dummy_product');
                assert($dummyProduct instanceof ProductInterface);
                $dummyStyle = $container->get('inpsyde_payment_gateway.dummy_style');
                assert($dummyStyle instanceof StyleInterface);
                $system = $container->get('inpsyde_payment_gateway.system');
                assert($system instanceof SystemInterface);
                $storeCountry = 'US';
                $client = $clientFactory->createClientForApi($uriFactory->createUri($credentials['url']), $tokenProviderFactory->createBasicProvider($credentials['code'], $credentials['token']));
                $payoneer = $payoneerFactory->createPayoneerForApi($client);
                $transactionId = sprintf('tr-%1$d-credentials-test', time());
                $division = !empty($credentials['division']) ? (string) $credentials['division'] : '';
                $createListCommand = $payoneer->getListCommand()->withApiClient($client)->withTransactionId($transactionId)->withCountry($storeCountry)->withCallback($dummyCallback)->withCustomer($dummyCustomer)->withPayment($dummyPayment)->withProducts([$dummyProduct])->withStyle($dummyStyle)->withOperationType('PRESET')->withSystem($system)->withIntegrationType(PayoneerIntegrationTypes::SELECTIVE_NATIVE)->withDivision($division);
                try {
                    $createListCommand->execute();
                } catch (CommandExceptionInterface $exception) {
                    return 'Failed to create LIST session. Credentials should be considered invalid.';
                }
                return null;
            };
        },
        'inpsyde_payment_gateway.api_credentials_validator' => new Constructor(CallbackValidator::class, ['inpsyde_payment_gateway.api_credentials_validator_callback']),
        'inpsyde_payment_gateway.payment_fields_container_id' => static function () : string {
            return 'payoneer-payment-fields-container';
        },
        'inpsyde_payment_gateway.list_url_container_id' => static function () : string {
            return 'payoneer-list-url';
        },
        'inpsyde_payment_gateway.exclude_not_supported_countries' => new Constructor(ExcludeNotSupportedCountries::class, ['inpsyde_payment_gateway.not_supported_countries']),
        'inpsyde_payment_gateway.asset_customizer.payment_widget_css_options' => new Value([]),
        'inpsyde_payment_gateway.token_placeholder' => new Value('*****'),
        'inpsyde_payment_gateway.settings_option_key' => new StringService('woocommerce_{0}_settings', ['inpsyde_payment_gateway.gateway.id']),
        'inpsyde_payment_gateway.has_fields' => static function () : bool {
            return \true;
        },
        /**
         * A utility function that allows generator-style mapping.
         */
        'inpsyde_payment_gateway.fn.map' => new Factory([], static function () : callable {
            return static function (iterable $things, callable $mapper) : array {
                $things = $mapper($things);
                $map = [];
                while ($things->valid()) {
                    // phpcs:ignore Inpsyde.CodeQuality.ElementNameMinimalLength.TooShort
                    $k = $things->key();
                    /** @var array-key $k */
                    // phpcs:ignore Inpsyde.CodeQuality.ElementNameMinimalLength.TooShort
                    $map[$k] = $things->current();
                }
                return $map;
            };
        }),
        'inpsyde_payment_gateway.merchant.factory' => new Constructor(MerchantFactory::class, ['inpsyde_payment_gateway.uri_factory']),
        'inpsyde_payment_gateway.merchant.serializer' => new Constructor(MerchantSerializer::class, ['inpsyde_payment_gateway.merchant.default']),
        'inpsyde_payment_gateway.merchant.deserializer' => new Alias('inpsyde_payment_gateway.merchant.serializer'),
        /**
         * All merchants.
         */
        'inpsyde_payment_gateway.merchant.list' => new Factory(['inpsyde_payment_gateway.merchant.query', 'inpsyde_payment_gateway.merchant.list.default'], static function (MerchantQueryInterface $merchantQuery, iterable $defaultMerchants) : iterable {
            $merchants = $merchantQuery->execute();
            if (!count($merchants)) {
                $merchants = $defaultMerchants;
            }
            return $merchants;
        }),
        /**
         * A map of merchant label to instance.
         */
        'inpsyde_payment_gateway.merchant.map' => new Factory(['inpsyde_payment_gateway.merchant.list', 'inpsyde_payment_gateway.fn.map'], static function (iterable $merchants, callable $map) {
            $mapper = static function (iterable $merchants) : Generator {
                foreach ($merchants as $merchant) {
                    (yield $merchant->getLabel() => $merchant);
                }
            };
            $merchants = $map($merchants, $mapper);
            return $merchants;
        }),
        'inpsyde_payment_gateway.merchant.default' => new Factory(['inpsyde_payment_gateway.merchant.factory', 'inpsyde_payment_gateway.order.live_transactions_url_template', 'inpsyde_payment_gateway.order.sandbox_transactions_url_template', 'inpsyde_payment_gateway.order.checkout_transactions_url_template'], static function (MerchantFactoryInterface $merchantFactory, string $liveTransactionUrlTemplate, string $sandboxTransactionUrlTemplate, string $checkoutTransactionUrlTemplate) {
            $base = $merchantFactory->createMerchant(null);
            $orchestrationDecorator = new EnvironmentAwareUrlTemplateProvidingMerchant(['live' => $liveTransactionUrlTemplate, 'sandbox' => $sandboxTransactionUrlTemplate], $base);
            $checkoutDecorator = new CheckoutMerchantAwareUrlTemplateProvidingMerchant($checkoutTransactionUrlTemplate, $orchestrationDecorator);
            return $checkoutDecorator;
        }),
        'inpsyde_payment_gateway.merchant.storage_key' => new StringService('{0}_merchants', ['inpsyde_payment_gateway.gateway.id']),
        'inpsyde_payment_gateway.merchant.model' => new Constructor(ContainerMapMerchantModel::class, ['inpsyde_payment_gateway.storage', 'inpsyde_payment_gateway.merchant.storage_key', 'inpsyde_payment_gateway.merchant.serializer', 'inpsyde_payment_gateway.merchant.deserializer']),
        'inpsyde_payment_gateway.merchant.query' => new Alias('inpsyde_payment_gateway.merchant.model'),
        'inpsyde_payment_gateway.merchants_provider' => new Factory(['inpsyde_payment_gateway.merchant.query'], static function (MerchantQueryInterface $merchantQuery) : callable {
            return static function () use($merchantQuery) : iterable {
                return $merchantQuery->execute();
            };
        }),
        'inpsyde_payment_gateway.merchant.cmd.save' => new Alias('inpsyde_payment_gateway.merchant.model'),
        'inpsyde_payment_gateway.merchant.id' => new Factory(['inpsyde_payment_gateway.is_live_mode', 'inpsyde_payment_gateway.live_merchant_id', 'inpsyde_payment_gateway.sandbox_merchant_id'], static function (bool $liveMode, int $liveMerchantId, int $sandboxMerchantId) {
            return $liveMode ? $liveMerchantId : $sandboxMerchantId;
        }),
        'inpsyde_payment_gateway.merchant' => new Factory(['inpsyde_payment_gateway.merchant.id', 'inpsyde_payment_gateway.merchant.query', 'inpsyde_payment_gateway.merchant.default'], static function (int $id, MerchantQueryInterface $query, MerchantInterface $defaultMerchant) : MerchantInterface {
            $merchants = $query->withId($id)->execute();
            foreach ($merchants as $merchant) {
                return $merchant;
            }
            return $defaultMerchant;
        }),
        'inpsyde_payment_gateway.merchant.base_url' => new Factory(['inpsyde_payment_gateway.merchant'], static function (MerchantInterface $merchant) : UriInterface {
            return $merchant->getBaseUrl();
        }),
        'inpsyde_payment_gateway.merchant_code' => new Factory(['inpsyde_payment_gateway.merchant'], static function (MerchantInterface $merchant) : string {
            return $merchant->getCode();
        }),
        'inpsyde_payment_gateway.merchant_division' => new Factory(['inpsyde_payment_gateway.merchant'], static function (MerchantInterface $merchant) : string {
            return $merchant->getDivision();
        }),
        'inpsyde_payment_gateway.merchant_token' => new Factory(['inpsyde_payment_gateway.merchant'], static function (MerchantInterface $merchant) : string {
            return $merchant->getToken();
        }),
        'inpsyde_payment_gateway.settings_page_params' => new Factory(['inpsyde_payment_gateway.gateway.id'], static function (string $gatewayId) : array {
            return ['path' => 'wp-admin/admin.php', 'query' => ['page' => 'wc-settings', 'tab' => 'checkout', 'section' => $gatewayId]];
        }),
        'inpsyde_payment_gateway.assets.admin_settings_script.deps' => new Value([]),
        'inpsyde_payment_gateway.assets.admin_settings_script.handle' => new Value('payoneer-admin-settings-behaviour'),
        'inpsyde_payment_gateway.assets.admin_settings_script.version' => new Alias('inpsyde_payment_gateway.assets.version'),
        'inpsyde_payment_gateway.shop_url' => new Factory(['inpsyde_payment_gateway.uri_factory', 'inpsyde_payment_gateway.shop_url_string'], static function (UriFactoryInterface $uriFactory, string $shopUrl) : UriInterface {
            return $uriFactory->createUri($shopUrl);
        }),
        'inpsyde_payment_gateway.is_settings_page' => new Factory(['inpsyde_payment_gateway.page_detector', 'inpsyde_payment_gateway.settings_page_params'], static function (PageDetectorInterface $pageDetector, array $settingsPageParams) : bool {
            /** @psalm-suppress MixedArgumentTypeCoercion */
            return $pageDetector->isPage($settingsPageParams);
        }),
        /**
         * Scripts & Styles for Inpsyde Assets
         */
        'inpsyde_payment_gateway.path.assets' => new StringService('{0}/{1}/assets/', ['core.local_modules_directory_name', 'inpsyde_payment_gateway.module_name']),
        'inpsyde_payment_gateway.assets' => new ServiceList(['inpsyde_payment_gateway.assets.js.admin_settings']),
        'inpsyde_payment_gateway.assets.js.admin_settings.handle' => new Value('payoneer-admin-settings-behaviour'),
        'inpsyde_payment_gateway.assets.js.admin_settings.data' => new Value(['i18n' => ['confirmReset' => __('Are you sure you want to reset this field to its default value?', 'payoneer-checkout')]]),
        'inpsyde_payment_gateway.assets.js.admin_settings.can_enqueue' => new Factory(['inpsyde_payment_gateway.is_settings_page'], static function (bool $isSettingsPage) : callable {
            return static function () use($isSettingsPage) : bool {
                return $isSettingsPage;
            };
        }),
        'inpsyde_payment_gateway.assets.js.admin_settings' => new Factory(['core.main_plugin_file', 'inpsyde_payment_gateway.path.assets', 'inpsyde_payment_gateway.assets.js.admin_settings.handle', 'inpsyde_payment_gateway.assets.js.admin_settings.data', 'inpsyde_payment_gateway.assets.js.admin_settings.can_enqueue'], static function (string $mainPluginFile, string $assetsPath, string $handle, array $adminSettingsData, callable $canEnqueue) : Script {
            $url = plugins_url($assetsPath . 'admin-settings.js', $mainPluginFile);
            $script = new Script($handle, $url, Asset::BACKEND);
            $script->withLocalize('PayoneerData', $adminSettingsData);
            /** @psalm-suppress MixedArgumentTypeCoercion */
            $script->canEnqueue($canEnqueue);
            return $script;
        }),
        'inpsyde_payment_gateway.show_payment_widget_to_customers_in_sandbox_mode' => new Value(\false),
        /**
         * WC Settings API fields
         */
        'inpsyde_payment_gateway.settings_field_renderer.virtual' => new Constructor(VirtualField::class),
        'inpsyde_payment_gateway.settings_field_renderer.token' => new Constructor(TokenField::class),
        'inpsyde_payment_gateway.settings_field_sanitizer.token' => new Alias('inpsyde_payment_gateway.settings_field_renderer.token'),
        'inpsyde_payment_gateway.settings_field_renderer.plaintext' => new Constructor(PlainTextField::class),
    ];
};
