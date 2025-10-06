<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factory;
return new Factory(['payoneer_settings.token_placeholder', 'checkout.notification_received', 'inpsyde_payment_gateway.is_live_mode', 'inpsyde_payment_gateway.live_merchant_id', 'inpsyde_payment_gateway.sandbox_merchant_id', 'payoneer_sdk.remote_api_url.base_string.live', 'payoneer_sdk.remote_api_url.base_string.sandbox', 'payoneer_settings.merchant.label.live', 'payoneer_settings.merchant.label.sandbox'], static function (string $tokenPlaceholder, bool $notificationReceived, bool $liveMode, int $liveMerchantId, int $sandboxMerchantId, string $liveBaseUrl, string $sandboxBaseUrl, string $liveMerchantLabel, string $sandboxMerchantLabel): array {
    $liveModeCustomAttributes = [];
    if (!$notificationReceived && !$liveMode) {
        $liveModeCustomAttributes = ['disabled' => 'true'];
    }
    return ['enabled' => ['title' => \__('Enable/Disable', 'payoneer-checkout'), 'type' => 'checkbox', 'label' => \__('Enable Payoneer Checkout', 'payoneer-checkout'), 'default' => 'no', 'class' => 'section-payoneer-general'], 'live_mode' => ['title' => \__('Live mode', 'payoneer-checkout'), 'type' => 'checkbox', 'label' => \__('Enable Live mode', 'payoneer-checkout'), 'default' => 'no', 'class' => 'section-payoneer-general', 'custom_attributes' => $liveModeCustomAttributes], 'merchant_code' => ['title' => \__('API username', 'payoneer-checkout'), 'type' => 'text', 'description' => \__('Enter your API username here', 'payoneer-checkout'), 'desc_tip' => \true, 'class' => 'section-payoneer-general'], 'merchant_id' => [
        'type' => 'virtual',
        'group' => 'live_credentials',
        'group_role' => 'id',
        'class' => 'section-payoneer-general',
        //We need this to avoid overriding WC_Payment_Gateway::$settings of the value in
        //WC_Payment_Gateway::process_admin_options().
        //Same for the 'sandbox_merchant_id' field.
        'sanitize_callback' => static fn(): int => $liveMerchantId,
    ], 'merchant_token' => ['title' => \__('Live API token', 'payoneer-checkout'), 'type' => 'token', 'description' => 'Enter your merchant token here.', 'desc_tip' => \true, 'placeholder' => $tokenPlaceholder, 'group' => 'live_credentials', 'group_role' => 'token', 'class' => 'section-payoneer-general'], 'base_url' => ['type' => 'virtual', 'group' => 'live_credentials', 'group_role' => 'base_url', 'class' => 'section-payoneer-general', 'sanitize_callback' => static fn(): string => $liveBaseUrl], 'label' => ['type' => 'virtual', 'group' => 'live_credentials', 'group_role' => 'label', 'class' => 'section-payoneer-general', 'sanitize_callback' => static fn(): string => $liveMerchantLabel], 'store_code' => ['title' => \__('Live Store code', 'payoneer-checkout'), 'type' => 'text', 'description' => \__('Enter your Store code here', 'payoneer-checkout'), 'desc_tip' => \true, 'group' => 'live_credentials', 'group_role' => 'division', 'class' => 'section-payoneer-general'], 'sandbox_merchant_id' => [
        'type' => 'virtual',
        'group' => 'sandbox_credentials',
        'group_role' => 'id',
        'class' => 'section-payoneer-general',
        //for explanation of this see the comment for the merchant_id field above
        'sanitize_callback' => static fn(): int => $sandboxMerchantId,
    ], 'sandbox_merchant_token' => ['title' => \__('Test API token', 'payoneer-checkout'), 'type' => 'token', 'description' => 'Enter your sandbox merchant token here.', 'desc_tip' => \true, 'placeholder' => $tokenPlaceholder, 'group' => 'sandbox_credentials', 'group_role' => 'token', 'class' => 'section-payoneer-general'], 'sandbox_base_url' => ['type' => 'virtual', 'group' => 'sandbox_credentials', 'group_role' => 'base_url', 'class' => 'section-payoneer-general', 'sanitize_callback' => static fn(): string => $sandboxBaseUrl], 'sandbox_label' => ['type' => 'virtual', 'group' => 'sandbox_credentials', 'group_role' => 'label', 'class' => 'section-payoneer-general', 'sanitize_callback' => static fn(): string => $sandboxMerchantLabel], 'sandbox_store_code' => ['title' => \__('Test Store code', 'payoneer-checkout'), 'type' => 'text', 'description' => \__('Enter your Store code here', 'payoneer-checkout'), 'desc_tip' => \true, 'group' => 'sandbox_credentials', 'group_role' => 'division', 'class' => 'section-payoneer-general'], 'notification_received' => ['type' => 'virtual', 'default' => 'no']];
});
