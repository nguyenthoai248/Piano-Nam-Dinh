<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\Modifier;

class OrderShippingTaxModifier extends AbstractTaxModifier
{
    /**
     * @throws \WC_Data_Exception
     */
    public function modify() : void
    {
        if (is_null($this->order)) {
            return;
        }
        foreach ($this->modifiers as $modifier) {
            $shippingMethods = $this->order->get_shipping_methods();
            foreach ($shippingMethods as $shippingMethod) {
                /** @var array{total:array} $taxes */
                $taxes = $shippingMethod->get_taxes();
                $taxes['total'] = $this->overwriteOrderTaxes((string) $modifier->getTaxAmount());
                $shippingMethod->set_taxes($taxes);
            }
        }
    }
}
