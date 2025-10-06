<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Collection\MapFactoryInterface;
use Syde\Vendor\Dhii\Collection\MapInterface;
use Syde\Vendor\Dhii\Collection\MutableContainerInterface;
use Syde\Vendor\Dhii\Services\Factories\Alias;
use Syde\Vendor\Dhii\Services\Factories\Constructor;
use Syde\Vendor\Dhii\Services\Factories\ServiceList;
use Syde\Vendor\Dhii\Services\Factories\StringService;
use Syde\Vendor\Dhii\Services\Factories\Value;
use Syde\Vendor\Dhii\Services\Factory;
use Syde\Vendor\Dhii\Services\Service;
use Syde\Vendor\Inpsyde\Assets\Asset;
use Syde\Vendor\Inpsyde\Assets\Script;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PageDetector\PageDetectorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Fields\TokenField;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant\CheckoutMerchantAwareUrlTemplateProvidingMerchant;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant\ContainerMapMerchantModel;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant\EnvironmentAwareUrlTemplateProvidingMerchant;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant\MerchantFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant\MerchantFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant\MerchantInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant\MerchantQueryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant\MerchantSerializer;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Fields\CssField;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Fields\PlainTextField;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Fields\VirtualField;
use Syde\Vendor\Psr\Container\ContainerExceptionInterface;
use Syde\Vendor\Psr\Http\Message\UriInterface;
return static function (): array {
    $moduleRoot = \dirname(__FILE__, 2);
    return [
        'payoneer_settings.module_name' => new Value('payoneer-settings'),
        'payment_gateways.settings_field_renderer.plaintext' => new Constructor(PlainTextField::class),
        'payment_gateways.settings_field_renderer.css' => new Constructor(CssField::class),
        'payment_gateways.settings_field_renderer.token' => new Constructor(TokenField::class),
        'payment_gateways.settings_field_sanitizer.token' => new Alias('payment_gateways.settings_field_renderer.token'),
        'payment_gateways.settings_field_renderer.virtual' => new Constructor(VirtualField::class),
        'payoneer_settings.settings_fields' => Service::fromFile("{$moduleRoot}/inc/fields.php"),
        'payoneer_settings.settings_option_key' => new Value('woocommerce_payoneer-checkout_settings'),
        'payoneer_settings.settings_page_base_params' => new Value(['path' => 'wp-admin/admin.php', 'query' => ['page' => 'wc-settings', 'tab' => 'checkout']]),
        'payoneer_settings.payments_tab_page_params' => new Value(['path' => 'wp-admin/admin.php', 'query' => 'page=wc-settings&tab=checkout']),
        'payoneer_settings.assets.admin_settings_script.deps' => new Value([]),
        'payoneer_settings.assets.admin_settings_script.handle' => new Value('payoneer-admin-settings-behaviour'),
        /**
         * A utility function that allows generator-style mapping.
         */
        'payoneer_settings.fn.map' => new Factory([], static function (): callable {
            return static function (iterable $things, callable $mapper): array {
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
        'payoneer_settings.merchant.factory' => new Constructor(MerchantFactory::class, ['inpsyde_payment_gateway.uri_factory']),
        'payoneer_settings.merchant.serializer' => new Constructor(MerchantSerializer::class, ['payoneer_settings.merchant.default']),
        'payoneer_settings.merchant.deserializer' => new Alias('payoneer_settings.merchant.serializer'),
        /**
         * All merchants.
         */
        'payoneer_settings.merchant.list' => new Factory(['payoneer_settings.merchant.query', 'payoneer_settings.merchant.list.default'], static function (MerchantQueryInterface $merchantQuery, iterable $defaultMerchants): iterable {
            $merchants = $merchantQuery->execute();
            if (!\count($merchants)) {
                $merchants = $defaultMerchants;
            }
            return $merchants;
        }),
        'payoneer_settings.merchant.default' => new Factory(['payoneer_settings.merchant.factory', 'inpsyde_payment_gateway.order.live_transactions_url_template', 'inpsyde_payment_gateway.order.sandbox_transactions_url_template', 'inpsyde_payment_gateway.order.checkout_transactions_url_template'], static function (MerchantFactoryInterface $merchantFactory, string $liveTransactionUrlTemplate, string $sandboxTransactionUrlTemplate, string $checkoutTransactionUrlTemplate): MerchantInterface {
            $base = $merchantFactory->createMerchant(null);
            $orchestrationDecorator = new EnvironmentAwareUrlTemplateProvidingMerchant(['live' => $liveTransactionUrlTemplate, 'sandbox' => $sandboxTransactionUrlTemplate], $base);
            $checkoutDecorator = new CheckoutMerchantAwareUrlTemplateProvidingMerchant($checkoutTransactionUrlTemplate, $orchestrationDecorator);
            return $checkoutDecorator;
        }),
        'payoneer_settings.merchant.storage_key' => new Value('payoneer-checkout_merchants'),
        'payoneer_settings.merchant.model' => new Constructor(ContainerMapMerchantModel::class, ['inpsyde_payment_gateway.storage', 'payoneer_settings.merchant.storage_key', 'payoneer_settings.merchant.serializer', 'payoneer_settings.merchant.deserializer']),
        'payoneer_settings.merchant.query' => new Alias('payoneer_settings.merchant.model'),
        'payoneer_settings.merchants_provider' => new Factory(['payoneer_settings.merchant.query'], static function (MerchantQueryInterface $merchantQuery): callable {
            return static function () use ($merchantQuery): iterable {
                return $merchantQuery->execute();
            };
        }),
        'payoneer_settings.merchant.cmd.save' => new Alias('payoneer_settings.merchant.model'),
        'payoneer_settings.merchant.id' => new Factory(['inpsyde_payment_gateway.is_live_mode', 'inpsyde_payment_gateway.live_merchant_id', 'inpsyde_payment_gateway.sandbox_merchant_id'], static function (bool $liveMode, int $liveMerchantId, int $sandboxMerchantId): int {
            return $liveMode ? $liveMerchantId : $sandboxMerchantId;
        }),
        'payoneer_settings.merchant' => new Factory(['payoneer_settings.merchant.id', 'payoneer_settings.merchant.query', 'payoneer_settings.merchant.default'], static function (int $id, MerchantQueryInterface $query, MerchantInterface $defaultMerchant): MerchantInterface {
            $merchants = $query->withId($id)->execute();
            foreach ($merchants as $merchant) {
                return $merchant;
            }
            return $defaultMerchant;
        }),
        'payoneer_settings.merchant.base_url' => new Factory(['payoneer_settings.merchant'], static function (MerchantInterface $merchant): UriInterface {
            return $merchant->getBaseUrl();
        }),
        'payoneer_settings.merchant.label.sandbox' => fn(): string => \__('Test', 'payoneer-checkout'),
        'payoneer_settings.merchant.label.live' => fn(): string => \__('Live', 'payoneer-checkout'),
        'payoneer_settings.merchant_code' => new Factory(['payoneer_settings.merchant'], static function (MerchantInterface $merchant): string {
            return $merchant->getCode();
        }),
        'payoneer_settings.merchant_division' => new Factory(['payoneer_settings.merchant'], static function (MerchantInterface $merchant): string {
            return $merchant->getDivision();
        }),
        'payoneer_settings.merchant_token' => new Factory(['payoneer_settings.merchant'], static function (MerchantInterface $merchant): string {
            return $merchant->getToken();
        }),
        'payoneer_settings.is_settings_page' => new Factory(['inpsyde_payment_gateway.page_detector', 'payoneer_settings.settings_page_base_params', 'payoneer-settings.settings-tabs'], static function (PageDetectorInterface $pageDetector, array $settingsPageParams, array $settingsSections): bool {
            $payoneerSections = \array_keys($settingsSections);
            /** @psalm-suppress MixedArgumentTypeCoercion */
            foreach ($payoneerSections as $section) {
                /**
                 * //It seems like we cannot operate with psalm types here, so we need to
                 * provide the full array structure here and below.
                 * @var array{
                 * scheme?: string,
                 * host?: string,
                 * user?: string,
                 * pass?: string,
                 * port?: int,
                 * path?: string | string[],
                 * query?: string | array<string, string>,
                 * fragment?: string,
                 * } $params
                 */
                $params = \array_merge($settingsPageParams, ['query' => ['section' => $section]]);
                if ($pageDetector->isPage($params)) {
                    return \true;
                }
            }
            return \false;
        }),
        'payoneer_settings.is_settings_page_payoneer-general_tab' => new Factory(['inpsyde_payment_gateway.page_detector', 'payoneer_settings.settings_page_base_params'], static function (PageDetectorInterface $pageDetector, array $settingsPageParams): bool {
            $generalTabPageParams = \array_merge($settingsPageParams, ['query' => ['section' => 'payoneer-general']]);
            return $pageDetector->isPage($generalTabPageParams);
        }),
        'payoneer_settings.is_payments_settings_page' => new Factory(
            ['inpsyde_payment_gateway.page_detector', 'payoneer_settings.payments_tab_page_params'],
            /**
             * @psalm-param array{
             *  scheme?: string,
             *  host?: string,
             *  user?: string,
             *  pass?: string,
             *  port?: int,
             *  path?: string | string[],
             *  query?: string | array<string, string>,
             *  fragment?: string,
             *  } $paymentSettingsPageParams
             */
            static function (PageDetectorInterface $pageDetector, array $paymentSettingsPageParams): bool {
                return $pageDetector->isPage($paymentSettingsPageParams);
            }
        ),
        'payoneer_settings.assets.js.admin_settings.can_enqueue' => new Factory(['payoneer_settings.is_settings_page'], static function (bool $isSettingsPage): callable {
            return static function () use ($isSettingsPage): bool {
                return $isSettingsPage;
            };
        }),
        'payoneer_settings.assets.js.admin_settings' => new Factory(['core.main_plugin_file', 'payoneer_settings.path.assets', 'payoneer_settings.assets.js.admin_settings.handle', 'payoneer_settings.assets.js.admin_settings.data', 'payoneer_settings.assets.js.admin_settings.can_enqueue'], static function (string $mainPluginFile, string $assetsPath, string $handle, array $adminSettingsData, callable $canEnqueue): Script {
            $url = \plugins_url($assetsPath . 'admin-settings.js', $mainPluginFile);
            $script = new Script($handle, $url, Asset::BACKEND);
            $script->withLocalize('PayoneerData', $adminSettingsData);
            /** @psalm-suppress MixedArgumentTypeCoercion */
            $script->canEnqueue($canEnqueue);
            return $script;
        }),
        'payoneer_settings.assets.js.payment_methods.data' => new Factory(['core.http.settings_url', 'payment_methods.all'], static function (UriInterface $generalSettingsUrl, $paymentMethods) {
            return [
                'paymentMethods' => $paymentMethods,
                /* translators: Help tip displayed next to the greyed-out toggle on the Payments settings page */
                'helpTipMessage' => \esc_html__('Payoneer payment methods are de/activated globally on the gateway settings page', 'payoneer-checkout'),
                'generalSettingsUrl' => (string) $generalSettingsUrl,
            ];
        }),
        'payoneer_settings.assets.js.payment_methods.handle' => new Value('payoneer-payment-methods'),
        'payoneer_settings.assets.js.payment_methods.can_enqueue' => new Factory(['payoneer_settings.is_payments_settings_page'], static function (bool $isPaymentSettingsPage): callable {
            return static function () use ($isPaymentSettingsPage): bool {
                return $isPaymentSettingsPage;
            };
        }),
        'payoneer_settings.assets.js.payment_methods' => new Factory(['core.main_plugin_file', 'payoneer_settings.path.assets', 'payoneer_settings.assets.js.payment_methods.handle', 'payoneer_settings.assets.js.payment_methods.data', 'payoneer_settings.assets.js.payment_methods.can_enqueue'], static function (string $mainPluginFile, string $assetsPath, string $handle, array $adminSettingsData, callable $canEnqueue): Script {
            $url = \plugins_url($assetsPath . 'admin-payment-methods.js', $mainPluginFile);
            $script = new Script($handle, $url, Asset::BACKEND);
            $script->withLocalize('PayoneerData', $adminSettingsData);
            /** @psalm-suppress MixedArgumentTypeCoercion */
            $script->canEnqueue($canEnqueue);
            return $script;
        }),
        'payoneer_settings.assets' => new ServiceList(['payoneer_settings.assets.js.admin_settings', 'payoneer_settings.assets.js.payment_methods']),
        /**
         * Scripts & Styles for Inpsyde Assets
         */
        'payoneer_settings.path.assets' => new StringService('{0}/{1}/assets/', ['core.local_modules_directory_name', 'payoneer_settings.module_name']),
        'payoneer_settings.assets.js.admin_settings.data' => static fn() => ['i18n' => ['confirmReset' => \__('Are you sure you want to reset this field to its default value?', 'payoneer-checkout')]],
        'payoneer_settings.assets.js.admin_settings.handle' => new Value('payoneer-admin-settings-behaviour'),
        'payoneer_settings.token_placeholder' => new Value('*****'),
        'payoneer_settings.options' => new Factory(
            ['wp.sites.current.options', 'payment_methods.default_options', 'payoneer_settings.settings_option_key', 'core.data.structure_based_factory'],
            /** @psalm-suppress InvalidCatch */
            static function (MutableContainerInterface $siteOptions, array $defaults, string $optionKey, MapFactoryInterface $datastructureBasedFactory): MapInterface {
                try {
                    $value = $siteOptions->get($optionKey);
                } catch (ContainerExceptionInterface $exception) {
                    $value = [];
                }
                if (!\is_array($value)) {
                    throw new \UnexpectedValueException(\sprintf('Gateway options for key "%1$s" must be an array', $optionKey));
                }
                /** @var array<string, mixed> $value */
                $value += $defaults;
                $product = $datastructureBasedFactory->createContainerFromArray($value);
                return $product;
            }
        ),
        'payoneer-settings.settings-tabs' => static fn() => [
            /**
             * We register once settings tab for general merchant credentials and global settings
             * Other modules may add additional tabs via service extensions
             */
            /* translators: Title of the settings tab */
            'payoneer-general' => \__('Payoneer Checkout', 'payoneer-checkout'),
        ],
        'payoneer-settings.merchant-credentials.is-entered' => new Factory(['payoneer_settings.merchant'], static function (MerchantInterface $merchant): bool {
            return $merchant->getCode() && $merchant->getToken() && $merchant->getDivision();
        }),
    ];
};
