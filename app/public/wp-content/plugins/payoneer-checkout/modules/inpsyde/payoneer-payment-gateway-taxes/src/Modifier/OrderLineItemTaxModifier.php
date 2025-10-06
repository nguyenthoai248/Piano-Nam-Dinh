<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\Modifier;

class OrderLineItemTaxModifier extends AbstractTaxModifier
{
    public function modify() : void
    {
        if (is_null($this->order)) {
            return;
        }
        foreach ($this->modifiers as $modifier) {
            $lineItem = $this->findLineItem($this->order->get_items(), $modifier->getProductId());
            if (!$lineItem || !is_a($lineItem, \WC_Order_Item_Product::class)) {
                return;
            }
            /** @var array{subtotal:array,total:array} $itemTaxes */
            $itemTaxes = $lineItem->get_taxes();
            $itemTaxes['subtotal'] = $this->overwriteOrderTaxes((string) $modifier->getTaxAmount());
            $itemTaxes['total'] = $this->overwriteOrderTaxes((string) $modifier->getTaxAmount());
            $lineItem->set_taxes($itemTaxes);
        }
    }
    private function findLineItem(?array $lineItems, string $productId) : ?\WC_Order_Item_Product
    {
        if (!$lineItems) {
            return null;
        }
        /** @var \WC_Order_Item_Product $lineItem */
        foreach ($lineItems as $lineItem) {
            $lineItemData = $lineItem->get_data();
            if (!empty($lineItemData['variation_id'])) {
                $lineItemId = $lineItemData['variation_id'];
            } else {
                $lineItemId = $lineItemData['product_id'];
            }
            if ($lineItemId == $productId) {
                return $lineItem;
            }
        }
        return null;
    }
}
