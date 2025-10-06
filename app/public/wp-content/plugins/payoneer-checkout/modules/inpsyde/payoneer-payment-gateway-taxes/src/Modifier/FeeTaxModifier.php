<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\Modifier;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\TaxesModule;
class FeeTaxModifier extends AbstractTaxModifier
{
    public function modify() : void
    {
        if (is_null($this->cart)) {
            return;
        }
        $total = floatval($this->cart->get_total(''));
        $taxTotal = $this->cart->get_taxes_total();
        $feeTaxOld = $this->cart->get_fee_tax();
        $feeTax = [];
        foreach ($this->modifiers as $modifier) {
            $fee = $this->findFee($modifier->getProductId());
            if (!$fee) {
                do_action('payoneer-checkout.cart_item_not_found_for_tax_modifier', $this->cart, $modifier->getProductId());
                continue;
            }
            $fee->tax_data = [TaxesModule::TAX_RATE_ID => $modifier->getTaxAmount()];
            if (!isset($feeTax[TaxesModule::TAX_RATE_ID])) {
                $feeTax[TaxesModule::TAX_RATE_ID] = 0.0;
            }
            $feeTax[TaxesModule::TAX_RATE_ID] += $modifier->getTaxAmount();
        }
        $feeTaxSum = array_sum($feeTax);
        /**
         * Fee tax is added automatically.
         * Without subtracting the old fee tax, we receive a net amount error.
         */
        $taxTotal -= $feeTaxOld;
        $total += -$feeTaxOld + $feeTaxSum;
        $this->cart->set_fee_tax((string) $feeTaxSum);
        $this->cart->set_fee_taxes($feeTax);
        $this->cart->set_total((string) $total);
        $this->cart->set_total_tax((string) $taxTotal);
    }
    private function findFee(string $feeId) : ?object
    {
        if (is_null($this->cart)) {
            return null;
        }
        $fees = $this->cart->fees_api()->get_fees();
        /** @var object{id:string} $fee */
        foreach ($fees as $fee) {
            if ($fee->id === $feeId) {
                return $fee;
            }
        }
        return null;
    }
}
