<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout;

use Syde\Vendor\Dhii\Services\Factories\Constructor;
use Syde\Vendor\Dhii\Services\Factory;
use Syde\Vendor\Inpsyde\Assets\Script;
use Syde\Vendor\Inpsyde\Assets\Style;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\Modifier\DiscountTaxModifier;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\Modifier\FeeTaxModifier;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\Modifier\LineItemTaxModifier;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\Modifier\ShippingTaxModifier;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\TaxConfigurationChecker;
return static function () : array {
    return ['taxes.path.assets' => new Factory(['core.local_modules_directory_name'], static function (string $modulesDirectoryRelativePath) : string {
        $moduleRelativePath = sprintf('%1$s/%2$s', $modulesDirectoryRelativePath, 'payoneer-payment-gateway-taxes');
        return sprintf('%1$s/assets/', $moduleRelativePath);
    }), 'taxes.assets.css' => new Factory(['core.main_plugin_file', 'taxes.path.assets', 'core.is_checkout'], static function (string $mainPluginFile, string $assetsPath, bool $isCheckout) : Style {
        $url = plugins_url($assetsPath . 'taxes.css', $mainPluginFile);
        $style = new Style('taxes', $url);
        $style->canEnqueue($isCheckout);
        return $style;
    }), 'taxes.assets.js' => new Factory(['core.main_plugin_file', 'taxes.path.assets', 'core.is_checkout'], static function (string $mainPluginFile, string $assetsPath, bool $isCheckout) : Script {
        $url = plugins_url($assetsPath . 'taxes.js', $mainPluginFile);
        $script = new Script('payoneer-payment-gateway-taxes', $url);
        $script->canEnqueue($isCheckout);
        return $script;
    }), 'taxes.assets' => new Factory(['taxes.assets.js', 'taxes.assets.css'], static function (Script $taxesJs, Style $taxesCSS) : array {
        return [$taxesJs, $taxesCSS];
    }), 'wc.shipping.packages' => static function () {
        return WC()->shipping()->packages;
    }, 'inpsyde_payment_gateway.line_item_tax_modifier' => new Factory(['wc.cart'], static function (\WC_Cart $cart) {
        return new LineItemTaxModifier($cart);
    }), 'inpsyde_payment_gateway.fee_tax_modifier' => new Factory(['wc.cart'], static function (\WC_Cart $cart) {
        return new FeeTaxModifier($cart);
    }), 'inpsyde_payment_gateway.shipping_tax_modifier' => new Factory(['wc.cart', 'wc.shipping.packages'], static function (\WC_Cart $cart, array $shippingPackages) {
        $shippingTaxModifier = new ShippingTaxModifier($cart);
        $shippingTaxModifier->setShippingPackages($shippingPackages);
        return $shippingTaxModifier;
    }), 'inpsyde_payment_gateway.discount_tax_modifier' => new Factory(['wc.cart'], static function (\WC_Cart $cart) : DiscountTaxModifier {
        return new DiscountTaxModifier($cart);
    }), 'inpsyde_payment_gateway.tax_configuration_checker' => new Constructor(TaxConfigurationChecker::class)];
};
