<?php

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
// phpcs:disable Inpsyde.CodeQuality.NoAccessors.NoGetter
// phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
// phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway;

use Syde\Vendor\Dhii\Collection\MapInterface;
use Syde\Vendor\Dhii\Validation\Exception\ValidationFailedExceptionInterface;
use Syde\Vendor\Dhii\Validation\ValidatorInterface;
use Exception;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Exception\PaymentGatewayException;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Exception\PaymentGatewayExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\CommandFactory\WcOrderBasedUpdateCommandFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\PaymentFieldsRenderer\PaymentFieldsRendererInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\RefundProcessor\RefundProcessorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Settings\SettingsFieldRendererInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Settings\SettingsFieldSanitizerInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant\MerchantDeserializerInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant\MerchantInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant\MerchantQueryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant\SaveMerchantCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\PaymentProcessor\PaymentProcessorInterface;
use Syde\Vendor\Psr\Container\ContainerExceptionInterface;
use Syde\Vendor\Psr\Container\ContainerInterface;
use RangeException;
use RuntimeException;
use UnexpectedValueException;
use WC_Order;
use WC_Order_Refund;
use WC_Payment_Gateway;
use WP_Error;
/**
 * Payoneer Payment gateway.
 *
 * We need to disable rules below for the PaymentGateway class because it
 * extends WC_Payment_Gateway. We haven't this class in the development
 * environment, but using stubs instead. These stubs have no methods
 * parameters defined, so we cannot let psalm know what parameters are expected.
 * Additionally, WC_Payment_Gateway class and its parent WC_Settings_API are not
 * well-typed, so it causes a lot of false error reports too.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-suppress InvalidPropertyAssignmentValue
 * @psalm-suppress MixedOperand
 * @psalm-suppress MissingParamType
 * @psalm-suppress MixedArrayOffset
 * @psalm-suppress MixedArgument
 * @psalm-type MerchantData = array{
 *  id?: int,
 *  label?: string,
 *  code?: string,
 *  token?: string,
 *  base_url?: string,
 *  transaction_url_template?: string
 * }
 */
class PaymentGateway extends WC_Payment_Gateway
{
    protected const TRANSACTION_URL_TEMPLATE_FIELD_NAME = '_transaction_url_template';
    /**
     * Payment gateway configuration.
     *
     * @var ContainerInterface
     */
    protected $config;
    /**
     * @var string
     */
    protected $optionKey;
    /** @var MapInterface */
    protected $options;
    /** @var MerchantQueryInterface */
    protected $merchantQuery;
    /** @var SaveMerchantCommandInterface */
    protected $saveMerchantCommand;
    /** @var MerchantDeserializerInterface */
    protected $merchantDeserializer;
    /**
     * @var string
     */
    protected $refundLongIdCache;
    /**
     * @var string
     */
    protected $payoutIdFieldName;
    /**
     * @var WcOrderBasedUpdateCommandFactoryInterface
     */
    protected $updateCommandFactory;
    /** @var MerchantInterface */
    protected $merchant;
    /**
     * @var PaymentRequestValidatorInterface
     */
    protected $paymentRequestValidator;
    /**
     * @var string
     */
    protected $refundReasonSuffixTemplate;
    /**
     * @var ContainerInterface
     */
    protected $serviceContainer;
    /**
     * @param ContainerInterface $config Configuration of the gateway.
     * @param array<array-key, array<array-key, mixed>> $fieldsConfig Gateway settings fields
     *          config (settings manageable by user).
     * @param PaymentFieldsRendererInterface $paymentFieldsRenderer Service able to render
     *          payment fields (normally displayed on the checkout).
     *
     * @param string $chargeIdFieldName Name of the field of the order where CHARGE ID is saved.
     *
     * @todo: refactor this to reduce number of dependencies.
     */
    public function __construct(ContainerInterface $serviceContainer, ContainerInterface $config, array $fieldsConfig, MapInterface $options, string $optionKey, string $payoutIdFieldName, MerchantInterface $merchant, MerchantQueryInterface $merchantQuery, SaveMerchantCommandInterface $saveMerchantCommand, MerchantDeserializerInterface $merchantDeserializer)
    {
        $this->serviceContainer = $serviceContainer;
        $this->form_fields = $fieldsConfig;
        $this->config = $config;
        $this->payoutIdFieldName = $payoutIdFieldName;
        $this->optionKey = $optionKey;
        $this->options = $options;
        $this->merchant = $merchant;
        $this->merchantQuery = $merchantQuery;
        $this->saveMerchantCommand = $saveMerchantCommand;
        $this->merchantDeserializer = $merchantDeserializer;
        $this->init_settings();
        $this->setPropertiesFromOptions();
        $this->setPropertiesFromConfig();
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        add_action('woocommerce_settings_checkout', [$this, 'display_errors']);
        $this->setupSavingPayoutData(function () : ?string {
            return $this->refundLongIdCache;
        });
        add_filter('woocommerce_settings_api_sanitized_fields_' . $this->id, [$this, 'filterVirtualFields']);
    }
    public function get_title() : string
    {
        $title = $this->options->get('live_mode') === 'no' ? __('Test:', 'payoneer-checkout') . ' ' . $this->title : $this->title;
        $title = wp_kses($title, ['br' => \true, 'img' => ['alt' => \true, 'class' => \true, 'src' => \true, 'title' => \true], 'p' => ['class' => \true], 'span' => ['class' => \true, 'title' => \true]]);
        $title = stripslashes($title);
        if (function_exists('force_balance_tags')) {
            $title = force_balance_tags($title);
        }
        return apply_filters('woocommerce_gateway_title', $title, $this->id);
    }
    /**
     * Detect payment gateway availability.
     *
     * @return bool Whether it is available, true for yes and false for no.
     */
    public function is_available() : bool
    {
        $isAvailable = parent::is_available();
        if (!$isAvailable) {
            return \false;
        }
        /**
         * This check doesn't make sense for admin pages.
         * But we don't want to always signalize false on the admin pages.
         * If our gateway is the only one enabled and completely functional, we may cause wrong
         * message from WooCommerce no gateways are set up.
         */
        if ($this->serviceContainer->get('wp.is_frontend_request')) {
            /**
             * Extra checks to prevent notices when there is no order to pay for
             * and cart is not initialized. WC_Payment_Gateway::get_order_total tries to get cart
             * total without checking whether cart exists.
             */
            $orderId = $this->serviceContainer->get('wc.pay_for_order_id');
            if (!$orderId && !wc()->cart instanceof \WC_Cart) {
                return \false;
            }
            $total = (float) $this->get_order_total();
            /**
             * This may look like duplicating parent method logic, but it is not.
             * Parent method returns false if  ! $total > 0 and ... and .... In practice,
             * it leaves payment method available when order amount is 0.
             */
            if (!$total > 0.0) {
                return \false;
            }
            if (did_action('payoneer-checkout.create_list_session_failed')) {
                return \false;
            }
        }
        $canBeUsed = $this->serviceContainer->get('inpsyde_payment_gateway.gateway.can_be_used');
        assert(is_callable($canBeUsed));
        return (bool) apply_filters('payoneer-checkout.payment_gateway_is_available', $canBeUsed(), $this);
    }
    /**
     * @inheritDoc
     */
    public function process_payment($orderId) : array
    {
        /**
         * Produce the WC_Order instance first
         * This should never fail unless there's a bug in WC
         * But we need to be verbose here
         */
        try {
            $order = $this->getOrder((string) $orderId);
            $paymentRequestValidator = $this->serviceContainer->get('inpsyde_payment_gateway.payment_request_validator');
            assert($paymentRequestValidator instanceof PaymentRequestValidatorInterface);
            $paymentRequestValidator->assertIsValid($order, $this);
        } catch (PaymentGatewayExceptionInterface $exception) {
            wc_add_notice($exception->getMessage(), 'error');
            WC()->session->set('refresh_totals', \true);
            return ['result' => 'failure', 'redirect' => ''];
        }
        $processor = $this->serviceContainer->get('inpsyde_payment_gateway.payment_processor');
        assert($processor instanceof PaymentProcessorInterface);
        return $processor->processPayment($order);
    }
    public function get_icon()
    {
        $hasIconService = $this->serviceContainer->has('checkout.gateway_icons_renderer');
        if (!$hasIconService) {
            return apply_filters('woocommerce_gateway_icon', "", $this->id);
        }
        $iconService = $this->serviceContainer->get('checkout.gateway_icons_renderer');
        $output = $iconService->renderIcons();
        return apply_filters('woocommerce_gateway_icon', $output, $this->id);
    }
    /**
     * Get order by ID or throw exception.
     *
     * @param string $orderId Order ID to get order by.
     *
     * @return WC_Order Found order.
     *
     * @throws PaymentGatewayExceptionInterface If order not found.
     */
    protected function getOrder(string $orderId) : WC_Order
    {
        $order = wc_get_order($orderId);
        if (!$order instanceof WC_Order) {
            throw new PaymentGatewayException(sprintf('Failed to process order %1$d, it cannot be found.', $orderId));
        }
        return $order;
    }
    public function getTransactionUrlFieldName() : string
    {
        return self::TRANSACTION_URL_TEMPLATE_FIELD_NAME;
    }
    /**
     * Get transaction URL template for order.
     *
     * @param $order
     *
     * @return string
     */
    public function get_transaction_url($order) : string
    {
        $this->view_transaction_url = (string) $order->get_meta(self::TRANSACTION_URL_TEMPLATE_FIELD_NAME, \true);
        return parent::get_transaction_url($order);
    }
    /**
     * @inheritDoc
     */
    public function process_refund($orderId, $amount = \null, $reason = '')
    {
        $order = wc_get_order($orderId);
        if (!$order instanceof WC_Order) {
            return new WP_Error('order_not_found', sprintf(
                /* translators: %1$s is replaced with the actual order ID. */
                __('Failed to process the refund: the order with ID %1$s not found', 'payoneer-checkout'),
                $orderId
            ));
        }
        $amount = floatval($amount);
        /**
         * Payoneer will not accept this amount, so we short-circuit here
         */
        if (empty($amount)) {
            return new WP_Error('invalid_amount', __('Cannot refund an amount of 0,00', 'payoneer-checkout'));
        }
        if ($reason === '') {
            //API requires refund reason not empty, but WooCommerce has this field optional.
            //That's why we are just providing default refund reason here.
            $reason = 'No refund reason provided.';
        }
        try {
            $refundProcessor = $this->serviceContainer->get('inpsyde_payment_gateway.refund_processor');
            assert($refundProcessor instanceof RefundProcessorInterface);
            $list = $refundProcessor->refundOrderPayment($order, $amount, $reason);
            $this->refundLongIdCache = $list->getIdentification()->getLongId();
        } catch (Exception $exception) {
            return new WP_Error('failed_to_refund_order_payment', __('Failed to refund the order payment', 'payoneer-checkout'));
        }
        return \true;
    }
    /**
     * @inheritDoc
     */
    public function payment_fields() : void
    {
        $renderer = $this->serviceContainer->get('inpsyde_payment_gateway.payment_fields_renderer');
        assert($renderer instanceof PaymentFieldsRendererInterface);
        try {
            //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $renderer->renderFields();
        } catch (\Throwable $exception) {
            do_action($this->id . '_payment_fields_failure', ['exception' => $exception]);
            // Print a generic error message right in the gateway fields
            /* translators: Placeholder text if payment fields failed to render */
            esc_html_e('Payment method not available. Please select another payment method.', 'payoneer-checkout');
        }
    }
    /**
     * Container-aware re-implementation of the parent method.
     * It first tries to find a dedicated service and falls back to the original implementation
     * if none is found.
     * @param $formFields
     * @param $echo
     *
     * @return string|void
     */
    public function generate_settings_html($formFields = [], $echo = \true)
    {
        if (empty($formFields)) {
            $formFields = $this->get_form_fields();
        }
        $html = '';
        foreach ($formFields as $key => $value) {
            $type = $this->get_field_type($value);
            try {
                /**
                 * Check if we have a dedicated renderer in our service container
                 */
                $fieldRenderer = $this->serviceContainer->get('inpsyde_payment_gateway.settings_field_renderer.' . $type);
                assert($fieldRenderer instanceof SettingsFieldRendererInterface);
                $html .= $fieldRenderer->render($key, $value, $this);
            } catch (ContainerExceptionInterface $exception) {
                /**
                 * Fallback to WC core implementation
                 */
                if (method_exists($this, 'generate_' . $type . '_html')) {
                    $html .= $this->{'generate_' . $type . '_html'}($key, $value);
                    continue;
                }
                if (has_filter('woocommerce_generate_' . $type . '_html')) {
                    $html .= apply_filters('woocommerce_generate_' . $type . '_html', '', $key, $value, $this);
                    continue;
                }
                $html .= $this->generate_text_html($key, $value);
            }
        }
        if ($echo) {
            // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $html;
            // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
        }
        return $html;
    }
    /**
     * @inheritDoc
     *
     * Adds support for groups.
     */
    public function get_custom_attribute_html($data)
    {
        if (!isset($data['custom_attributes'])) {
            $data['custom_attributes'] = [];
        }
        if (isset($data['group'])) {
            $data['custom_attributes']['group'] = $data['group'];
        }
        if (isset($data['group_role'])) {
            $data['custom_attributes']['group_role'] = $data['group_role'];
        }
        $html = parent::get_custom_attribute_html($data);
        return $html;
    }
    /**
     * @inheritDoc
     */
    public function process_admin_options() : bool
    {
        $this->processMerchants();
        /** @var callable():iterable<MerchantInterface> $merchantsProvider */
        $merchantsProvider = $this->serviceContainer->get('inpsyde_payment_gateway.merchants_provider');
        $merchants = $merchantsProvider();
        $this->validateMerchantCredentials($merchants);
        $result = parent::process_admin_options();
        return $result;
    }
    /**
     * Processes incoming merchant data.
     *
     * @throws RuntimeException If problem processing.
     */
    protected function processMerchants() : void
    {
        /*
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
        $merchants = [];
        foreach ($credentials as $key => $set) {
            $dto = ['code' => $code, 'environment' => $key] + $set;
            $merchant = $this->createMerchant($dto);
            $merchants[$key] = $merchant;
        }
        $this->setMerchants($merchants);
    }
    /**
     * Validates a set of API credentials.
     *
     * @param string $code The merchant code to validate.
     * @param string $token The merchant token to validate.
     * @param string $url The base URL of the API for which to validate the credentials.
     * @param string $division The division/store code associated with the merchant
     *
     * @throws ValidationFailedExceptionInterface If API credentials are invalid.
     * @throws RuntimeException If problem validating.
     */
    protected function validateApiCredentials(string $code, string $token, string $url, string $division)
    {
        $apiCredentialsValidator = $this->serviceContainer->get('inpsyde_payment_gateway.api_credentials_validator');
        assert($apiCredentialsValidator instanceof ValidatorInterface);
        $apiCredentialsValidator->validate(['code' => $code, 'token' => $token, 'url' => $url, 'division' => $division]);
    }
    /**
     * Retrieves the credentials to be validated.
     *
     * @return array<int, array<string, scalar>> A map of field role to field value.
     *
     * @throws RuntimeException If problem retrieving.
     */
    protected function getCredentialsToValidate() : array
    {
        $sets = ['sandbox' => $this->getFieldGroupValues('sandbox_credentials'), 'live' => $this->getFieldGroupValues('live_credentials')];
        return $sets;
    }
    /**
     * Retrieves a group of fields by group ID.
     *
     * @param string $id The ID of the group.
     *
     * @return array<string, string> A map of field role names to field names.
     */
    protected function getFieldGroupValues(string $id) : array
    {
        $group = $this->getFieldGroup($id);
        $group = array_map(function (string $fieldName) {
            return $this->getIncomingFieldValue($fieldName);
        }, $group);
        return $group;
    }
    /**
     * Retrieves a group of fields by group ID.
     *
     * @param string $id The ID of the group.
     *
     * @return array<string, string> A map of field role names to field names.
     */
    protected function getFieldGroup(string $id) : array
    {
        $fields = [];
        foreach ($this->get_form_fields() as $name => $config) {
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
     * Set class properties from provided config.
     */
    protected function setPropertiesFromConfig() : void
    {
        $this->id = $this->config->get('id');
        $this->view_transaction_url = $this->config->get('view_transaction_url');
        $this->pay_button_id = $this->config->get('pay_button_id');
        $this->tokens = $this->config->get('tokens');
        $this->method_title = $this->config->get('method_title');
        $this->method_description = $this->config->get('method_description');
        $this->max_amount = $this->config->get('max_amount');
        $this->supports = $this->config->get('supports');
        $this->refundReasonSuffixTemplate = $this->config->get('refund_reason_suffix_template');
    }
    /**
     * Set class properties from provided config.
     */
    protected function setPropertiesFromOptions() : void
    {
        $this->enabled = $this->get_option('enabled') === 'yes' ? 'yes' : 'no';
        $this->title = $this->get_option('title', '');
        $this->description = $this->get_option('description', '');
        $this->order_button_text = $this->get_option('order_button_text', '');
        $this->countries = $this->get_option('countries', []);
        $this->icon = $this->get_option('icon', '');
    }
    /**
     * Save Payout longId when WC_Order_Refund object is created.
     *
     * @param callable $payoutLongIdProvider
     *
     * @return void
     */
    protected function setupSavingPayoutData(callable $payoutLongIdProvider) : void
    {
        add_action('woocommerce_after_order_refund_object_save', function (WC_Order_Refund $refund) use($payoutLongIdProvider) : void {
            if (!$this->isRefundOrderPaidWithPayoneer($refund)) {
                return;
            }
            if ($refund->get_meta($this->payoutIdFieldName)) {
                return;
            }
            $payoutLongId = $payoutLongIdProvider($refund);
            if (!$payoutLongId) {
                return;
            }
            $refundReasonSuffix = sprintf($this->refundReasonSuffixTemplate, $payoutLongId);
            $refundReason = $refund->get_reason() . $refundReasonSuffix;
            $refund->set_reason($refundReason);
            $refund->add_meta_data($this->payoutIdFieldName, $payoutLongId);
            $refund->save();
        });
    }
    /**
     * Check if the order the given refund is for was paid via this payment gateway.
     *
     * @param WC_Order_Refund $refund Refund to check parent order payment method.
     *
     * @return bool
     */
    protected function isRefundOrderPaidWithPayoneer(WC_Order_Refund $refund) : bool
    {
        $parentOrderId = $refund->get_parent_id();
        $parentOrder = wc_get_order($parentOrderId);
        return $parentOrder instanceof WC_Order && $parentOrder->get_payment_method() === $this->id;
    }
    /**
     * @inheritDoc
     */
    public function get_option_key()
    {
        return $this->optionKey;
    }
    /**
     * @inheritDoc
     *
     * Makes sanitization container-aware.
     * If a  'inpsyde_payment_gateway.settings_field_sanitizer.' . $type service is found
     * then it is used instead of WC core sanitization.
     *
     * Additional exception handling is applied, so a RangeException thrown by any sanitization
     * will be rendered as an error
     */
    public function get_field_value($key, $field, $postData = [])
    {
        $type = $this->get_field_type($field);
        $fieldKey = $this->get_field_key($key);
        $postData = empty($postData) ? $_POST : $postData;
        // WPCS: CSRF ok, input var ok.
        $value = $postData[$fieldKey] ?? null;
        try {
            if (isset($field['sanitize_callback']) && is_callable($field['sanitize_callback'])) {
                return call_user_func($field['sanitize_callback'], $value);
            }
            try {
                /**
                 * Check if we have a dedicated field sanitizer in our service container
                 */
                $sanitizer = $this->serviceContainer->get('inpsyde_payment_gateway.settings_field_sanitizer.' . $type);
                assert($sanitizer instanceof SettingsFieldSanitizerInterface);
                return $sanitizer->sanitize($key, $value, $this);
            } catch (ContainerExceptionInterface $exception) {
                /**
                 * Fallback to WC core implementation
                 */
                // Look for a validate_FIELDID_field method for special handling.
                if (is_callable([$this, 'validate_' . $key . '_field'])) {
                    return $this->{'validate_' . $key . '_field'}($key, $value);
                }
                // Look for a validate_FIELDTYPE_field method.
                if (is_callable([$this, 'validate_' . $type . '_field'])) {
                    return $this->{'validate_' . $type . '_field'}($key, $value);
                }
                // Fallback to text.
                return $this->validate_text_field($key, $value);
            }
        } catch (RangeException $exception) {
            $this->add_error(sprintf('Field "%1$s" is invalid: %2$s', $key, $exception->getMessage()));
            return null;
        }
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
    protected function getFieldConfig(string $key) : array
    {
        $fields = $this->get_form_fields();
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
        /**
         * See https://github.com/woocommerce/woocommerce/issues/32512
         */
        $type = $this->get_field_type($field);
        // Virtual fields only available in storage.
        $value = $type === 'virtual' ? $this->get_option($key) : $this->get_field_value($key, $field);
        return $value;
    }
    /**
     * Returns the value of a field with the specified key.
     *
     * Allows defaults to be overridden.
     *
     * @param string $key The field key.
     *
     * @return scalar The value of the field.
     *
     * @throws RangeException If field not configured.
     * @throws RuntimeException If problem retrieving.
     */
    protected function getFieldValue(string $key)
    {
        $value = $this->getIncomingFieldValue($key);
        if ($value === '') {
            $value = $this->get_option($key);
        }
        return $value;
    }
    /**
     * @inheritDoc
     */
    public function init_settings() : void
    {
        $this->settings = iterator_to_array($this->options);
    }
    /**
     * Creates a new Merchant instance from data.
     *
     * @param array $dto The merchant data.
     * @psalm-param MerchantData $dto
     *
     * @return MerchantInterface The new instance.
     * @throws RuntimeException If problem creating.
     */
    protected function createMerchant(array $dto) : MerchantInterface
    {
        $merchant = $this->merchantDeserializer->deserializeMerchant($dto);
        return $merchant;
    }
    /**
     * Retrieves configured merchants.
     *
     * @return iterable<MerchantInterface> The merchants.
     * @throws RuntimeException If problem retrieving.
     */
    protected function getMerchants() : iterable
    {
        return $this->merchantQuery->execute();
    }
    /**
     * Assigns configured merchants.
     *
     * @param iterable<MerchantInterface> $merchants The merchants to assign.
     * @throws RuntimeException If problem retrieving.
     */
    protected function setMerchants(iterable $merchants) : void
    {
        $cmd = $this->saveMerchantCommand;
        foreach ($merchants as $merchant) {
            $cmd->saveMerchant($merchant);
        }
    }
    /**
     * For each merchant with invalid credentials, adds a form error.
     *
     * @param iterable<MerchantInterface> $merchants The merchants, whose credentials to validate.
     *
     * @throws RuntimeException If problem validating.
     */
    protected function validateMerchantCredentials(iterable $merchants) : void
    {
        foreach ($merchants as $merchant) {
            $code = $merchant->getCode();
            $token = $merchant->getToken();
            $url = (string) $merchant->getBaseUrl();
            $label = $merchant->getLabel();
            $division = $merchant->getDivision();
            try {
                $this->validateApiCredentials($code, $token, $url, $division);
            } catch (ValidationFailedExceptionInterface $exception) {
                $this->add_error(<<<TAG
Entered code and/or API token are invalid for merchant "{$label}".
Please, enter valid ones to be able to connect to Payoneer API.
TAG
);
            }
        }
    }
    public function filterVirtualFields(array $settings) : array
    {
        $validFields = array_filter($this->form_fields, static function (array $fieldConfig) {
            return $fieldConfig['type'] !== 'virtual';
        });
        $validKeys = array_keys($validFields);
        foreach ($settings as $key => $value) {
            if (!in_array($key, $validKeys, \true)) {
                unset($settings[$key]);
            }
        }
        return $settings;
    }
    public function has_fields()
    {
        return (bool) $this->serviceContainer->get('inpsyde_payment_gateway.has_fields');
    }
}
