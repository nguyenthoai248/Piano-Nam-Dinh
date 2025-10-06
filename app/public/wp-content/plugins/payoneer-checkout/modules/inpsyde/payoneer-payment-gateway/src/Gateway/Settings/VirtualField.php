<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Settings;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\PaymentGateway;
class VirtualField implements SettingsFieldRendererInterface
{
    public function render(string $fieldId, array $fieldConfig, PaymentGateway $gateway) : string
    {
        return '';
    }
}
