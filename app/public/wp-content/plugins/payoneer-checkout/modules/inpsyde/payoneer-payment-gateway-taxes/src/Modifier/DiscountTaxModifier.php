<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\Modifier;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\TaxesModule;
class DiscountTaxModifier extends AbstractTaxModifier
{
    public function modify() : void
    {
        if (!$this->cart instanceof \WC_Cart) {
            return;
        }
        if (count($this->modifiers) !== 1) {
            return;
        }
        $discountModifier = $this->modifiers[0];
        $newDiscountTax = $discountModifier->getTaxAmount();
        $total = floatval($this->cart->get_total(''));
        /** @var float[] $cartContentsTaxes */
        $cartContentsTaxes = $this->cart->get_cart_contents_taxes();
        $cartContentsTax = $this->cart->get_cart_contents_tax();
        $total += $newDiscountTax;
        $cartContentsTax += $newDiscountTax;
        if (!isset($cartContentsTaxes[TaxesModule::TAX_RATE_ID])) {
            $cartContentsTaxes[TaxesModule::TAX_RATE_ID] = 0.0;
        }
        $cartContentsTaxes[TaxesModule::TAX_RATE_ID] += $newDiscountTax;
        $this->cart->set_total((string) $total);
        $this->cart->set_total_tax((string) $cartContentsTax);
        $this->cart->set_cart_contents_taxes($cartContentsTaxes);
        $this->cart->set_cart_contents_tax((string) $cartContentsTax);
        $this->cart->set_discount_tax((string) $newDiscountTax);
    }
}
