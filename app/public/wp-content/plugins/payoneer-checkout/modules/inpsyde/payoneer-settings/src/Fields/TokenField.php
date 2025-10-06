<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Fields;

use Syde\Vendor\Inpsyde\PaymentGateway\PaymentGateway;
use Syde\Vendor\Inpsyde\PaymentGateway\SettingsFieldRendererInterface;
use Syde\Vendor\Inpsyde\PaymentGateway\SettingsFieldSanitizerInterface;
/**
 * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
 * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
 * @psalm-suppress MissingParamType
 */
class TokenField implements SettingsFieldRendererInterface, SettingsFieldSanitizerInterface
{
    public function render(string $fieldId, array $fieldConfig, PaymentGateway $gateway): string
    {
        $token = $gateway->get_option($fieldId);
        if ($token && isset($fieldConfig['placeholder'])) {
            $gateway->settings[$fieldId] = $fieldConfig['placeholder'];
        }
        $fieldHtml = $gateway->generate_password_html($fieldId, $fieldConfig);
        $gateway->settings[$fieldId] = $token;
        return $fieldHtml;
    }
    /**
     * @param string $key
     * @param mixed $value
     * @param PaymentGateway $gateway
     *
     * @return mixed|string
     */
    public function sanitize(string $key, $value, PaymentGateway $gateway)
    {
        $fields = $gateway->get_form_fields();
        $config = $fields[$key];
        if (isset($config['placeholder']) && $value === $config['placeholder']) {
            return $gateway->get_option($key);
        }
        return $gateway->validate_password_field($key, (string) $value);
    }
}
