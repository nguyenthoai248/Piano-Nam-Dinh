<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout;

use Syde\Vendor\Psr\Log\LogLevel;
return static function () : array {
    return ['inpsyde_logger.log_events' => static function (array $previous) : array {
        $logEventsToAdd = [
            //[
            //    'name' => 'payoneer-checkout.cart_item_not_found_for_tax_modifier',
            //    'log_level' => LogLevel::ERROR,
            //    'message' => static function (
            //        WC_Cart $cart,
            //        string $itemId = null
            //    ): string {
            //        return sprintf(
            //            'Failed to find cart item with id %1$s in cart %2$s',
            //            $itemId ?? '',
            //            json_encode($cart->get_cart()) ?: ''
            //        );
            //    },
            //],
            ['name' => 'payoneer-checkout.taxes_not_enabled', 'log_level' => LogLevel::INFO, 'message' => "Taxes are not enabled in WooCommerce settings."],
            ['name' => 'payoneer-checkout.invalid_tax_configuration', 'log_level' => LogLevel::NOTICE, 'message' => 'Payoneer Checkout detected an invalid tax configuration: {message}'],
        ];
        return array_merge($previous, $logEventsToAdd);
    }];
};
