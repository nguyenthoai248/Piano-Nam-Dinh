<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factory;
return new Factory(['embedded_payment.settings.checkout_css_custom_css.default'], static function (string $defaultCss): array {
    /**
     * Custom CSS was part of the previous payment-widget.
     * We still need this field registered to keep the contents in the database
     * in case a user decides to downgrade the plugin.
     */
    return ['checkout_css_custom_css' => ['type' => 'hidden', 'default' => $defaultCss, 'class' => 'section-payoneer-general', 'sanitize_callback' => static function ($value): string {
        return \wp_unslash(\wp_strip_all_tags((string) $value));
    }, 'custom_attributes' => ['readonly' => 'readonly']]];
});
