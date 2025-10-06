<?php

//phpcs:disable Inpsyde.CodeQuality.NoAccessors.NoSetter
declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings;

use Syde\Vendor\Dhii\Services\Factories\FuncService;
use Syde\Vendor\Dhii\Validation\Exception\ValidationFailedExceptionInterface;
use Syde\Vendor\Dhii\Validation\ValidatorInterface;
use Syde\Vendor\Inpsyde\Assets\Asset;
use Syde\Vendor\Inpsyde\Assets\AssetManager;
use Syde\Vendor\Inpsyde\Modularity\Module\ExecutableModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Syde\Vendor\Inpsyde\Modularity\Module\ServiceModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant\MerchantDeserializerInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant\MerchantInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant\SaveMerchantCommandInterface;
use Syde\Vendor\Psr\Container\ContainerExceptionInterface;
use Syde\Vendor\Psr\Container\ContainerInterface;
use Syde\Vendor\Psr\Container\NotFoundExceptionInterface;
use Syde\Vendor\Psr\Http\Message\UriInterface;
use RangeException;
use RuntimeException;
use UnexpectedValueException;
/**
 * @psalm-import-type MerchantData from MerchantDeserializerInterface
 */
class SettingsModule implements ServiceModule, ExecutableModule
{
    use ModuleClassNameIdTrait;
    /**
     * @inheritDoc
     * @phpcs:disable Inpsyde.CodeQuality.LineLength.TooLong
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
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     */
    public function run(ContainerInterface $container): bool
    {
        $paymentGatewayIds = $container->get('payment_gateways');
        $this->registerAssets($container);
        add_action('woocommerce_init', function () use ($container) {
            if (is_admin()) {
                $delegate = new FuncService(['inpsyde_payment_gateway.is_live_mode', 'inpsyde_payment_gateway.settings_page_url'], \Closure::fromCallable([$this, 'addSandboxNotice']));
                /** @psalm-suppress MixedFunctionCall */
                $delegate($container)();
            }
        });
        /**
         * Append gateway Icons to method title on Payment admin screen
         */
        add_action('woocommerce_settings_start', static function () use ($container) {
            $isPaymentsPage = $container->get('payoneer_settings.is_payments_settings_page');
            if (!$isPaymentsPage) {
                return;
            }
            add_filter('woocommerce_gateway_title', static function (string $title, string $gatewayId) use ($container): string {
                $payoneerMethods = $container->get('payment_methods.all');
                assert(is_array($payoneerMethods));
                if (!in_array($gatewayId, $payoneerMethods, \true)) {
                    return $title;
                }
                $gateway = wc()->payment_gateways()->payment_gateways()[$gatewayId] ?? null;
                if ($gateway instanceof \WC_Payment_Gateway) {
                    return $title . ' ' . $gateway->get_icon();
                }
                return $title;
            }, 10, 2);
        });
        $this->setUpPaymentPageAjaxCallback($container);
        $this->setUpProcessingMerchants($container);
        assert(is_array($paymentGatewayIds));
        $this->setUpTriggeringSettingsSaving();
        $this->setUpSettingsPageRendering($container);
        $this->setUpDisplayingSections($container);
        $this->setUpMergeGatewaySettings($container);
        $this->setUpAdminPageStyle($container);
        $this->setUpLinkToGeneralTabIfNotConnected($container);
        $this->setUpPaymentSettingsPageStyle($container);
        return \true;
    }
    /**
     * Intercept the AJAX call that toggles a gateway in the "Payments" tab
     * Merchants cannot configure the state of individual methods, so we cause
     * WooCommerce to redirect to the setup page
     *
     * @phpcs:disable Inpsyde.CodeQuality.NestingLevel.High
     */
    public function setUpPaymentPageAjaxCallback(ContainerInterface $container): void
    {
        $gatewayId = filter_input(\INPUT_POST, 'gateway_id', \FILTER_CALLBACK, ['options' => 'sanitize_key']);
        if (current_user_can('manage_woocommerce') && check_ajax_referer('woocommerce-toggle-payment-gateway-enabled', 'security', \false) && $gatewayId !== null) {
            $paymentGatewayIds = $container->get('payment_gateways');
            add_action('wp_ajax_woocommerce_toggle_gateway_enabled', static function () use ($paymentGatewayIds, $gatewayId) {
                assert(is_array($paymentGatewayIds));
                if (!in_array($gatewayId, $paymentGatewayIds, \true)) {
                    return;
                }
                wp_send_json_error('needs_setup');
            }, \PHP_INT_MIN);
        }
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
            /** @var Asset[] $assets */
            $assets = $container->get('payoneer_settings.assets');
            $assetManager->register(...$assets);
        });
    }
    public function addSandboxNotice(bool $liveMode, UriInterface $settingsPageUrl): void
    {
        if ($liveMode) {
            return;
        }
        add_action('all_admin_notices', static function () use ($settingsPageUrl): void {
            $class = 'notice notice-warning';
            $aTagOpening = sprintf('<a href="%1$s">', (string) $settingsPageUrl);
            $disableTestMode = sprintf(
                /* translators: %1$s, %2$s and %3$s are replaced with the opening and closing 'a' tags */
                esc_html__('%1$sEnable live mode%2$s when you are ready to accept Live transactions.', 'payoneer-checkout'),
                $aTagOpening,
                '</a>',
                '<a href="">'
            );
            printf('<div class="%1$s"><h4>%2$s</h4><p>%3$s</p></div>', esc_attr($class), esc_html__('Payoneer Checkout Live mode is disabled', 'payoneer-checkout'), wp_kses($disableTestMode, ['a' => ['href' => []]], ['http', 'https']));
        }, 11);
    }
    /**
     * Return instance of the payment gateway responsible for handling settings.
     *
     * @return \WC_Payment_Gateway
     */
    protected function getMainGateway(): \WC_Payment_Gateway
    {
        static $paymentGateway;
        if (!$paymentGateway instanceof \WC_Payment_Gateway) {
            $paymentGateway = wc()->payment_gateways()->payment_gateways()['payoneer-checkout'] ?? null;
            if (!$paymentGateway instanceof \WC_Payment_Gateway) {
                throw new \UnexpectedValueException();
            }
        }
        return $paymentGateway;
    }
    /**
     * @param ContainerInterface $container
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function setUpSettingsPageRendering(ContainerInterface $container): void
    {
        add_action('woocommerce_settings_checkout', function () use ($container): void {
            $paymentGatewaysIds = $container->get('payment_gateways');
            assert(is_array($paymentGatewaysIds));
            if (isset($GLOBALS['current_section']) && in_array($GLOBALS['current_section'], $paymentGatewaysIds, \true)) {
                //For gateways sections settings fields are rendered automatically by WC.
                return;
            }
            /** @var array<string, string> $payoneerSections */
            $payoneerSections = $container->get('payoneer-settings.settings-tabs');
            if (!$this->isPayoneerSection($payoneerSections)) {
                return;
            }
            $this->getMainGateway()->admin_options();
        });
    }
    protected function setUpTriggeringSettingsSaving(): void
    {
        add_action('woocommerce_update_options_checkout_payoneer-general', static function (): void {
            do_action('woocommerce_update_options_payment_gateways_payoneer-checkout');
        });
    }
    /**
     * @param array<string, string> $payoneerSections
     */
    protected function setUpDisplayingSections(ContainerInterface $container): void
    {
        add_filter(
            'woocommerce_get_sections_checkout',
            /**
             * @param mixed $currentSections
             *
             * @return mixed|string[]
             */
            function ($currentSections) use ($container) {
                /** @var array<string, string> $payoneerSections */
                $payoneerSections = $container->get('payoneer-settings.settings-tabs');
                if (!$this->isPayoneerSection($payoneerSections)) {
                    return $currentSections;
                }
                return $payoneerSections;
            }
        );
    }
    protected function isPayoneerSection(array $payoneerSections): bool
    {
        /** @psalm-var array<string, mixed> $GLOBALS */
        if (!isset($GLOBALS['current_section'])) {
            return \false;
        }
        return array_key_exists((string) $GLOBALS['current_section'], $payoneerSections);
    }
    /**
     * Processes incoming merchant data.
     *
     * @throws RuntimeException If problem processing.
     */
    protected function processMerchants(SaveMerchantCommandInterface $saveMerchantCommand, MerchantDeserializerInterface $merchantDeserializer): void
    {
        /**
         * This causes field value retrieval, which in turn causes validation due to WC.
         * The parent method catches them, and turns validation errors into error notices in UI.
         * The parent method also retrieves the values of all configured fields.
         * This means that a value for a field may be retrieved, and thus validated, many times.
         * This means that, even if caught, this may result in many errors for the same problem.
         * Due to the lack of a centralized way of retrieving incoming fields (WC will use original)
         * there is no way to avoid this - at least not without further refactoring.
         */
        $credentials = $this->getCredentialsToValidate();
        $code = $this->getIncomingFieldValue('merchant_code');
        assert(is_string($code));
        $merchants = [];
        foreach ($credentials as $key => $set) {
            $dto = ['code' => $code, 'environment' => $key] + $set;
            $merchant = $merchantDeserializer->deserializeMerchant($dto);
            $merchants[$key] = $merchant;
        }
        $this->setMerchants($merchants, $saveMerchantCommand);
    }
    /**
     * Assigns configured merchants.
     *
     * @param iterable<MerchantInterface> $merchants The merchants to assign.
     * @throws RuntimeException If problem retrieving.
     */
    protected function setMerchants(iterable $merchants, SaveMerchantCommandInterface $saveMerchantCommand): void
    {
        foreach ($merchants as $merchant) {
            $saveMerchantCommand->saveMerchant($merchant);
        }
    }
    /**
     * For each merchant with invalid credentials, adds a form error.
     *
     * @param iterable<MerchantInterface> $merchants The merchants, whose credentials to validate.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function validateMerchantCredentials(iterable $merchants, ValidatorInterface $apiCredentialsValidator): void
    {
        foreach ($merchants as $merchant) {
            $code = $merchant->getCode();
            $token = $merchant->getToken();
            $url = (string) $merchant->getBaseUrl();
            $label = $merchant->getLabel();
            $division = $merchant->getDivision();
            try {
                $this->validateApiCredentials($code, $token, $url, $division, $apiCredentialsValidator);
            } catch (ValidationFailedExceptionInterface $exception) {
                $gateway = $this->getMainGateway();
                $gateway->add_error(<<<TAG
Entered code and/or API token are invalid for merchant "{$label}".
Please, enter valid ones to be able to connect to Payoneer API.
TAG
);
            }
        }
    }
    /**
     * Validates a set of API credentials.
     *
     * @param string $code The merchant code to validate.
     * @param string $token The merchant token to validate.
     * @param string $url The base URL of the API for which to validate the credentials.
     * @param string $division The division/store code associated with the merchant
     * @param ValidatorInterface $apiCredentialsValidator
     * @throws ValidationFailedExceptionInterface If API credentials are invalid.
     */
    protected function validateApiCredentials(string $code, string $token, string $url, string $division, ValidatorInterface $apiCredentialsValidator): void
    {
        $apiCredentialsValidator->validate(['code' => $code, 'token' => $token, 'url' => $url, 'division' => $division]);
    }
    /**
     * Retrieves the credentials to be validated.
     *
     * @return array<string, MerchantData> A map of field role to field value.
     *
     * @throws RuntimeException If problem retrieving.
     */
    protected function getCredentialsToValidate(): array
    {
        $sets = ['sandbox' => $this->getFieldGroupValues('sandbox_credentials'), 'live' => $this->getFieldGroupValues('live_credentials')];
        /** @var array<string, MerchantData> $sets */
        return $sets;
    }
    /**
     * Retrieves a group of fields by group ID.
     *
     * @param string $id The ID of the group.
     *
     * @return array<string, scalar> A map of field role names to field names.
     */
    protected function getFieldGroupValues(string $id): array
    {
        $group = $this->getFieldGroup($id);
        $group = array_map(function (string $fieldName) {
            return $this->getIncomingFieldValue($fieldName);
        }, $group);
        return $group;
    }
    /**
     * Retrieves the incoming value of a field with the specified name.
     *
     * @param string $key The field key.
     *
     * @return scalar The value of the field.
     *
     * @throws RangeException If field not configured.
     * @throws RuntimeException If problem retrieving.
     */
    protected function getIncomingFieldValue(string $key)
    {
        $field = $this->getFieldConfig($key);
        $paymentGateway = $this->getMainGateway();
        /**
         * See https://github.com/woocommerce/woocommerce/issues/32512
         */
        $type = $paymentGateway->get_field_type($field);
        // Virtual fields only available in storage.
        $value = $type === 'virtual' ? $paymentGateway->get_option($key) : $paymentGateway->get_field_value($key, $field);
        return $value;
    }
    /**
     * Retrieves configuration for a field.
     *
     * @param string $key The key of the field.
     *
     * @return array The field configuration.
     *
     * @throws RangeException If field not configured.
     * @throws RuntimeException If problem retrieving.
     */
    protected function getFieldConfig(string $key): array
    {
        $paymentGateway = $this->getMainGateway();
        $fields = $paymentGateway->get_form_fields();
        if (!isset($fields[$key])) {
            throw new RangeException(sprintf('Field "%1$s" is not configured', $key));
        }
        $field = $fields[$key];
        if (!is_array($field)) {
            throw new UnexpectedValueException(sprintf('Invalid configuration for field "%1$s"', $key));
        }
        return $field;
    }
    /**
     * Retrieves a group of fields by group ID.
     *
     * @param string $id The ID of the group.
     *
     * @return array<string, string> A map of field role names to field names.
     */
    protected function getFieldGroup(string $id): array
    {
        $fields = [];
        $gateway = $this->getMainGateway();
        /** @var array<string, array<string, string>> $formFields */
        $formFields = $gateway->get_form_fields();
        foreach ($formFields as $name => $config) {
            if (isset($config['group']) && $config['group'] === $id) {
                $key = $config['group_role'] ?? $name;
                $fields[$key] = $name;
            }
        }
        if (!count($fields)) {
            throw new RangeException(sprintf('No fields belong to group "%1$s"', $id));
        }
        return $fields;
    }
    /**
     * Merge payment gateway settings retrieved by WC with settings retrieved by the plugin.
     *
     * Payment gateway settings are initialized by WooCommerce automatically.
     * Most of them has the same values as the gateway settings we retrieve
     * from service. But there are some important fields we need to inject into
     * the regular settings, for example sandbox/live merchants ids. Also, it is
     * useful for integrations like debug plugin that may need to add something
     * specific to settings.
     *
     * @param ContainerInterface $container
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function setUpMergeGatewaySettings(ContainerInterface $container): void
    {
        add_action('payoneer-checkout_after_init_settings', static function (\WC_Payment_Gateway $gateway) use ($container) {
            $gatewayOptions = $container->get('inpsyde_payment_gateway.options');
            assert($gatewayOptions instanceof \Traversable);
            $gateway->settings = array_merge($gateway->settings, iterator_to_array($gatewayOptions));
        });
    }
    protected function setUpProcessingMerchants(ContainerInterface $container): void
    {
        add_filter('woocommerce_settings_api_sanitized_fields_payoneer-checkout', function ($fields) use ($container) {
            $currentSection = $GLOBALS['current_section'] ?? null;
            if ($currentSection !== 'payoneer-general') {
                return $fields;
            }
            $saveMerchantCommand = $container->get('payoneer_settings.merchant.cmd.save');
            assert($saveMerchantCommand instanceof SaveMerchantCommandInterface);
            $merchantDeserializer = $container->get('payoneer_settings.merchant.deserializer');
            assert($merchantDeserializer instanceof MerchantDeserializerInterface);
            $this->processMerchants($saveMerchantCommand, $merchantDeserializer);
            /** @var callable():iterable<MerchantInterface> $merchantsProvider */
            $merchantsProvider = $container->get('payoneer_settings.merchants_provider');
            $merchants = $merchantsProvider();
            $apiCredentialsValidator = $container->get('inpsyde_payoneer_api.api_credentials_validator');
            assert($apiCredentialsValidator instanceof ValidatorInterface);
            $this->validateMerchantCredentials($merchants, $apiCredentialsValidator);
            return $fields;
        });
    }
    public function setUpPaymentSettingsPageStyle(ContainerInterface $container): void
    {
        add_action('admin_head', static function () use ($container): void {
            if (!$container->get('payoneer_settings.is_payments_settings_page')) {
                return;
            }
            echo wp_kses(<<<STYLE
<style>
.payoneer-gateway-icons img {
    height: 24px;
    width: auto;
    margin-left: 4px;
    vertical-align: middle;
}
</style>
STYLE
, ['style' => []]);
        });
    }
    public function setUpAdminPageStyle(ContainerInterface $container): void
    {
        add_action('admin_head', function () use ($container): void {
            /** @var array<string, string> $payoneerSections */
            $payoneerSections = $container->get('payoneer-settings.settings-tabs');
            if (!$this->isPayoneerSection($payoneerSections)) {
                return;
            }
            $currentSection = sanitize_key((string) $GLOBALS['current_section']);
            $style = "<style type='text/css' id='payoneer-checkout-admin-style'>\n                        h3[class*=section-payoneer-],\n                        table.form-table tr:has([class*=section-payoneer-]) {\n                            display:none\n                        }\n                        h3.section-{$currentSection} {\n                            display: block;\n                        }\n                        table.form-table tr:has(.section-{$currentSection}) {\n                            display: table-row;\n                        }\n                      </style>";
            echo wp_kses($style, ['style' => ['type' => [], 'id' => []]]);
        });
    }
    /**
     * Prevent merchants from visiting individual payment gateway settings pages until the merchant credentials
     * have been entered.
     * This is arguably a little crude and we should find a better solution.
     * TODO Remove/Refactor this when WC 9.7+ releases the revamped Payment Settings UX
     *
     * @param ContainerInterface $container
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function setUpLinkToGeneralTabIfNotConnected(ContainerInterface $container): void
    {
        /**
         * Defer to admin_init since we anticipate a call to get_rest_url() which cannot run early
         * This has previously led to issues if admin_url() is called very early in the request)
         */
        add_action('admin_init', static function () use ($container) {
            add_filter('admin_url', static function ($url, $path) use ($container) {
                if (!is_string($url)) {
                    return $url;
                }
                if (!$container->get('payoneer_settings.is_payments_settings_page')) {
                    return $url;
                }
                if ($container->get('payoneer-settings.merchant-credentials.is-entered')) {
                    return $url;
                }
                $gatewayIds = $container->get('payment_gateways');
                foreach ($gatewayIds as $gatewayId) {
                    assert(is_string($gatewayId));
                    if ($path === 'admin.php?page=wc-settings&tab=checkout&section=' . $gatewayId) {
                        return (string) $container->get('core.http.settings_url');
                    }
                }
                return $url;
            }, 10, 2);
        });
    }
}
