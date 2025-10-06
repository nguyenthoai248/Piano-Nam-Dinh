<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\Modifier;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\TaxesModule;
use WC_Shipping_Rate;
class ShippingTaxModifier extends AbstractTaxModifier
{
    /** @var null | array */
    protected $packages = null;
    public function setShippingPackages(array $packages) : void
    {
        $this->packages = $packages;
    }
    public function modify() : void
    {
        if (is_null($this->cart) || is_null($this->packages)) {
            return;
        }
        $total = floatval($this->cart->get_total(''));
        $taxTotal = $this->cart->get_taxes_total();
        $shippingTaxOld = $this->cart->get_shipping_tax();
        $totalShippingTax = array_reduce($this->modifiers, static function (float $currentTotal, TaxModifierItem $modifier) {
            $currentTotal += $modifier->getTaxAmount();
            return $currentTotal;
        }, 0.0);
        /** @var array{rates: WC_Shipping_Rate[]} $package */
        foreach ($this->packages as $package) {
            if (empty($package['rates'])) {
                continue;
            }
            array_walk($package['rates'], static function (WC_Shipping_Rate $rate) use($totalShippingTax) {
                $rate->set_taxes([TaxesModule::TAX_RATE_ID => $totalShippingTax]);
            });
        }
        /**
         * Shipping tax is added automatically.
         * Without subtracting the old shipping tax, we receive a net amount error.
         */
        $taxTotal -= $shippingTaxOld;
        $total += -$shippingTaxOld + $totalShippingTax;
        $this->cart->set_shipping_taxes([TaxesModule::TAX_RATE_ID => $totalShippingTax]);
        $this->cart->set_shipping_tax((string) $totalShippingTax);
        $this->cart->set_total((string) $total);
        $this->cart->set_total_tax((string) $taxTotal);
    }
}
