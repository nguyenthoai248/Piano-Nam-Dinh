<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\Modifier;

use WC_Order_Item_Fee;
class OrderFeeTaxModifier extends AbstractTaxModifier
{
    public function modify() : void
    {
        if (is_null($this->order)) {
            return;
        }
        foreach ($this->modifiers as $modifier) {
            $orderFees = $this->getSanitizedFees($this->order->get_fees());
            /** @var \WC_Order_Item_Fee $orderFee */
            if (empty($orderFees[$modifier->getProductId()])) {
                continue;
            }
            $orderFee = $orderFees[$modifier->getProductId()];
            /** @var array{total:array} $taxes */
            $taxes = $orderFee->get_taxes();
            if (!isset($taxes['total'])) {
                continue;
            }
            $taxes['total'] = $this->overwriteOrderTaxes((string) $modifier->getTaxAmount());
            $orderFee->set_taxes($taxes);
        }
    }
    /**
     * @param WC_Order_Item_Fee[] $fees
     * @return array<string, WC_Order_Item_Fee>
     */
    protected function getSanitizedFees(array $fees) : array
    {
        if (count($fees) === 0) {
            return $fees;
        }
        return array_reduce(
            $fees,
            /**
             * @param array<string, WC_Order_Item_Fee> $assocFees
             * @return array<string, WC_Order_Item_Fee>
             */
            static function (array $assocFees, \WC_Order_Item_Fee $fee) : array {
                $assocFees[sanitize_title($fee->get_name())] = $fee;
                return $assocFees;
            },
            []
        );
    }
}
