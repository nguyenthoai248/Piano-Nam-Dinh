<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\WebSdk\Config\StylesColor;
return new Factory(['embedded_payment.is_enabled'], static function (bool $embeddedModeEnabled): array {
    $fields = [];
    foreach (StylesColor::OPTIONS as $option) {
        $fields["checkout_color_{$option}"] = ['title' => StylesColor::title($option), 'type' => 'color', 'class' => 'section-payoneer-general', 'description' => \sprintf(
            /** translators: %s: Description of a WebSDK color field */
            \__('%s (Empty: use default color)', 'payoneer-checkout'),
            StylesColor::description($option)
        ), 'custom_attributes' => $embeddedModeEnabled ? [] : ['readonly' => 'readonly']];
    }
    return $fields;
});
