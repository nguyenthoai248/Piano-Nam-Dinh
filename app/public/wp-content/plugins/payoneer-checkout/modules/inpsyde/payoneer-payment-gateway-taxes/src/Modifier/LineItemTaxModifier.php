<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\Modifier;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\TaxesModule;
class LineItemTaxModifier extends AbstractTaxModifier
{
    public function modify() : void
    {
        if (is_null($this->cart)) {
            return;
        }
        if (count($this->modifiers) === 0) {
            return;
        }
        $total = floatval($this->cart->get_total(''));
        /** @var float[] $cartContentsTaxes */
        $cartContentsTaxes = $this->cart->get_cart_contents_taxes();
        $taxTotal = $this->cart->get_taxes_total();
        foreach ($this->modifiers as $modifier) {
            $taxModifierLineItemConnector = TaxModifierLineItemConnector::fromCart($this->cart, $modifier->getProductId());
            $lineItemKey = $taxModifierLineItemConnector->getLineItemKey();
            if (!(isset($this->cart->cart_contents[$lineItemKey]) && is_array($this->cart->cart_contents[$lineItemKey]))) {
                //todo: consider refactoring to avoid duplication
                do_action('payoneer-checkout.cart_item_not_found_for_tax_modifier', $this->cart, $lineItemKey);
                continue;
            }
            $currentTax = floatval($this->cart->cart_contents[$lineItemKey]['line_tax']);
            $newTax = -$currentTax + $modifier->getTaxAmount();
            $total += $newTax;
            $taxTotal += $newTax;
            /**
             * We need to add this tax anyway, no matter if the tax rate already existed or not in
             * cart contents taxes array. Before, we only added a new tax if the tax rate already
             * existed in this array. But it turned out to be wrong and only caused a problems.
             */
            if (!isset($cartContentsTaxes[TaxesModule::TAX_RATE_ID])) {
                $cartContentsTaxes[TaxesModule::TAX_RATE_ID] = 0.0;
            }
            $cartContentsTaxes[TaxesModule::TAX_RATE_ID] += $newTax;
            $this->cart->cart_contents[$lineItemKey]['line_subtotal_tax'] = $modifier->getTaxAmount();
            $this->cart->cart_contents[$lineItemKey]['line_tax'] = $modifier->getTaxAmount();
            $this->setLineItemTaxData($lineItemKey, 'subtotal', $modifier->getTaxAmount());
            $this->setLineItemTaxData($lineItemKey, 'total', $modifier->getTaxAmount());
        }
        if (count($cartContentsTaxes) > 0) {
            $this->cart->set_cart_contents_taxes($cartContentsTaxes);
            $this->cart->set_cart_contents_tax((string) array_sum(array_values($cartContentsTaxes)));
        }
        $this->cart->set_total((string) $total);
        $this->cart->set_total_tax((string) $taxTotal);
    }
    private function setLineItemTaxData(string $lineItemKey, string $property, float $newValue) : void
    {
        if (!isset($this->cart->cart_contents[$lineItemKey]['line_tax_data'][$property])) {
            return;
        }
        $lineItemTaxData = $this->cart->cart_contents[$lineItemKey]['line_tax_data'][$property] ?? [];
        if (!is_array($lineItemTaxData)) {
            return;
        }
        foreach (array_keys($lineItemTaxData) as $lineItemSubTotalKey) {
            if (isset($this->cart->cart_contents[$lineItemKey]['line_tax_data'][$property][$lineItemSubTotalKey]) && is_array($this->cart->cart_contents[$lineItemKey]['line_tax_data']) && is_array($this->cart->cart_contents[$lineItemKey]['line_tax_data'][$property])) {
                $this->cart->cart_contents[$lineItemKey]['line_tax_data'][$property][$lineItemSubTotalKey] = $newValue;
            }
        }
    }
}
