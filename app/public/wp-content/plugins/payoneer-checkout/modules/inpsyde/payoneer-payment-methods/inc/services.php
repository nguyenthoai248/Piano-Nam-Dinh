<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factories\Alias;
use Syde\Vendor\Dhii\Services\Factories\Constructor;
use Syde\Vendor\Dhii\Services\Factories\Value;
use Syde\Vendor\Dhii\Services\Factory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\CommandFactory\WcOrderBasedUpdateCommandFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\Authentication\TokenGeneratorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\MisconfigurationDetector\MisconfigurationDetectorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\AvailabilityCallback\LiveModeAvailabilityCallback;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\ExcludeNotSupportedCountries;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\PaymentProcessor\PayoneerCommonPaymentProcessor;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\RefundProcessor;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Admin\AsyncRefundGlobalNotices;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Admin\RefundFailureEmailSender;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant\MerchantInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Admin\AsyncRefundAdminUi;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\RefundTextContents;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Service\RefundState;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Service\RefundOrchestrator;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Storage\AsyncFailedRefundRegistry;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Storage\AsyncRefundIntentStorage;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Storage\AsyncRefundStatusStorage;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Storage\PayoutToRefundMapping;
use Syde\Vendor\Psr\Container\ContainerInterface;
return static function (): array {
    return [
        'payment_methods.module_root_path' => static function (): string {
            return \dirname(__DIR__);
        },
        'payment_methods.path.assets' => new Factory(['core.local_modules_directory_name'], static function (string $modulesDirectoryRelativePath): string {
            $moduleRelativePath = \sprintf('%1$s/%2$s', $modulesDirectoryRelativePath, 'payoneer-payment-methods');
            return \sprintf('%1$s/assets/', $moduleRelativePath);
        }),
        'payment_methods.fallback_title' => fn() => \__('Pay with', 'payoneer-checkout'),
        'payment_methods.payoneer-checkout.method_description.payments_settings_page' => static function (): string {
            $description = \__('Payoneer Checkout is the next generation of payment processing platforms.', 'payoneer-checkout');
            $descriptionLegal = \sprintf(
                /* translators: %1$s, %2$s, %3$s and %4$s are replaced with opening and closing 'a' tags. */
                \__('By using Payoneer Checkout, you agree to the %1$sTerms of Service%2$s and %3$sPrivacy policy%4$s.', 'payoneer-checkout'),
                '<a href="https://www.payoneer.com/legal-agreements/?cnty=HK" target="_blank">',
                '</a>',
                '<a target="_blank" href="https://www.payoneer.com/legal/privacy-policy/">',
                '</a>'
            );
            return \sprintf('<p>%1$s</p><p>%2$s</p>', $description, $descriptionLegal);
        },
        'payment_methods.payoneer-checkout.method_description.settings_page' => new Factory([], static function (): string {
            return \sprintf(
                /* translators: %1$s, %2$s, %3$s, %4$s, %5$s and %6$s is replaced with the opening and closing 'a' tags.*/
                \__('Before you begin read How to %1$sConnect WooCommerce%2$s to Payoneer Checkout. Make sure you have a Payoneer Account. If you don\'t, see %3$sRegister for Checkout%4$s. You can get your %5$sauthentication data%6$s in the Payoneer Account.', 'payoneer-checkout'),
                '<a href="https://checkoutdocs.payoneer.com/docs/integrate-with-woocommerce" target="_blank">',
                '</a>',
                '<a href="https://www.payoneer.com/solutions/checkout/woocommerce-integration/?utm_source=Woo+plugin&utm_medium=referral&utm_campaign=WooCommerce+config+page#form-modal-trigger" target="_blank">',
                '</a>',
                '<a href="https://myaccount.payoneer.com/ma/checkout/tokens" target="_blank">',
                '</a>'
            );
        }),
        //todo: think about moving this to factories
        'payment_methods.availability_callback.checkout_predicate' => new Alias('list_session.can_try_create_list.callable'),
        'payment_methods.availability_callback.live_mode' => new Constructor(LiveModeAvailabilityCallback::class, ['payment_methods.is_live_mode', 'wc.admin_permission', 'payment_methods.show_payment_widget_to_customers_in_sandbox_mode']),
        'payment_methods.live_merchant_id' => new Value(1),
        'payment_methods.sandbox_merchant_id' => new Value(2),
        'payment_methods.default_options' => new Factory(['payoneer_sdk.remote_api_url.base_string.live', 'payoneer_sdk.remote_api_url.base_string.sandbox', 'payment_methods.live_merchant_id', 'payment_methods.sandbox_merchant_id', 'payoneer_settings.merchant.label.live', 'payoneer_settings.merchant.label.sandbox'], static function (string $liveUrl, string $sandboxUrl, int $liveMerchantId, int $sandboxMerchantId, string $liveLabel, string $sandboxLabel): array {
            return ['live_mode' => 'no', 'merchant_id' => $liveMerchantId, 'base_url' => $liveUrl, 'label' => $liveLabel, 'sandbox_merchant_id' => $sandboxMerchantId, 'sandbox_base_url' => $sandboxUrl, 'sandbox_label' => $sandboxLabel];
        }),
        'payment_methods.transaction_url_template_field_name' => new Value('_transaction_url_template'),
        'payment_methods.network_icon_map' => new Value(['VISA' => 'visa', 'MASTERCARD' => 'mastercard', 'AMEX' => 'amex', 'DISCOVER' => 'discover', 'DINERS' => 'diners', 'JCB' => 'jcb', 'AFTERPAY' => 'afterpay', 'KLARNA' => 'klarna', 'AFFIRM' => 'affirm', 'BANCONTACT' => 'bancontact', 'EPS' => 'eps', 'IDEAL' => 'ideal', 'MULTIBANCO' => 'multibanco', 'P24' => 'p24', 'UNIONPAY' => 'unionpay']),
        'payment_methods.payoneer-checkout.default_icons' => new Value(['visa', 'mastercard', 'amex', 'discover', 'diners', 'jcb', 'unionpay']),
        'payment_methods.payoneer-hosted.default_icons' => new Value(['visa', 'mastercard', 'amex', 'discover', 'diners', 'jcb', 'unionpay', 'afterpay', 'klarna', 'affirm', 'bancontact', 'eps', 'ideal', 'multibanco', 'p24']),
        'payment_methods.payoneer-afterpay.default_icons' => new Value(['afterpay']),
        'payment_methods.payoneer-klarna.default_icons' => new Value(['klarna']),
        'payment_methods.payoneer-affirm.default_icons' => new Value(['affirm']),
        'payment_methods.payoneer-bancontact.default_icons' => new Value(['bancontact']),
        'payment_methods.payoneer-eps.default_icons' => new Value(['eps']),
        'payment_methods.payoneer-ideal.default_icons' => new Value(['ideal']),
        'payment_methods.payoneer-multibanco.default_icons' => new Value(['multibanco']),
        'payment_methods.payoneer-p24.default_icons' => new Value(['p24']),
        'payment_methods.exclude_not_supported_countries' => new Constructor(ExcludeNotSupportedCountries::class, ['payment_methods.not_supported_countries']),
        'payment_methods.is_live_mode' => new Factory(['inpsyde_payment_gateway.options'], static function (ContainerInterface $options): bool {
            $optionValue = $options->get('live_mode');
            $optionValue = $optionValue !== 'no';
            return $optionValue;
        }),
        'payment_methods.show_payment_widget_to_customers_in_sandbox_mode' => '__return_false',
        'payment_methods.common_payment_processor' => new Factory(['embedded_payment.misconfiguration_detector', 'list_session.manager', 'inpsyde_payoneer_api.update_command_factory', 'checkout.security_token_generator', 'checkout.order.security_header_field_name', 'payment_methods.order.transaction_id_field_name', 'checkout.session_hash_key', 'payment_methods.transaction_url_template_field_name', 'payoneer_settings.merchant_id_field_name', 'payoneer_settings.merchant'], static function (MisconfigurationDetectorInterface $misconfigurationDetector, ListSessionProvider $sessionProvider, WcOrderBasedUpdateCommandFactoryInterface $updateCommandFactory, TokenGeneratorInterface $tokenGenerator, string $tokenKey, string $transactionIdFieldName, string $sessionHashKey, string $transactionUrlTemplateFieldName, string $merchantIdFieldName, MerchantInterface $merchant) {
            return new PayoneerCommonPaymentProcessor($misconfigurationDetector, $sessionProvider, $updateCommandFactory, $tokenGenerator, $tokenKey, $transactionIdFieldName, $sessionHashKey, $transactionUrlTemplateFieldName, $merchantIdFieldName, $merchant);
        }),
        // Refunds.
        'payment_methods.common.refund_processor' => new Constructor(RefundProcessor::class, ['inpsyde_payment_gateway.payoneer', 'inpsyde_payment_gateway.transaction_id_field_name', 'inpsyde_payment_gateway.payment_factory', 'inpsyde_payment_gateway.charge_id_field_name', 'wp.is_ajax', 'wp.refund.service.orchestrator']),
        'wp.refund.storage.failed_refund_registry' => new Constructor(AsyncFailedRefundRegistry::class),
        'wp.refund.storage.async_refund_intent' => new Constructor(AsyncRefundIntentStorage::class),
        'wp.refund.storage.async_refund_status' => new Constructor(AsyncRefundStatusStorage::class),
        'wp.refund.storage.payout_to_refund_mapping' => new Constructor(PayoutToRefundMapping::class, ['webhooks.order_refund.payout_id_field_name']),
        'wp.refund.service.refund_state' => new Constructor(RefundState::class, ['wp.refund.storage.async_refund_status']),
        'wp.refund.text_contents' => new Constructor(RefundTextContents::class),
        'wp.refund.service.orchestrator' => new Constructor(RefundOrchestrator::class, ['payment_gateways', 'wp.refund.text_contents', 'wp.refund.storage.payout_to_refund_mapping', 'wp.refund.service.refund_state', 'wp.refund.storage.async_refund_intent', 'wp.refund.storage.failed_refund_registry', 'wp.refund.admin.refund_failure_email_sender']),
        'wp.refund.admin.async_refund_order_ui' => new Constructor(AsyncRefundAdminUi::class, ['wp.refund.text_contents', 'wp.refund.service.orchestrator', 'wp.refund.service.refund_state', 'wp.order_admin.details_page']),
        'wp.refund.admin.async_refund_global_notices' => new Constructor(AsyncRefundGlobalNotices::class, ['wp.refund.text_contents', 'wp.refund.storage.failed_refund_registry', 'wp.admin_notice.renderer', 'wp.order_admin.details_page']),
        'wp.refund.admin.refund_failure_email_sender' => new Constructor(RefundFailureEmailSender::class, ['wp.refund.text_contents']),
    ];
};
