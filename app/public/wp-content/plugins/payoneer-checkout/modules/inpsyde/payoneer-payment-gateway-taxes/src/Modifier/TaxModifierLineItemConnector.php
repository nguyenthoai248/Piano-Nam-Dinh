<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\Modifier;

use WC_Product;
class TaxModifierLineItemConnector
{
    /** @var string $lineItemKey */
    private $lineItemKey;
    public function __construct(string $lineItemKey)
    {
        $this->lineItemKey = $lineItemKey;
    }
    public function getLineItemKey() : string
    {
        return $this->lineItemKey;
    }
    public static function fromCart(\WC_Cart $cart, string $productId) : self
    {
        /**
         * @var string $lineItemKey
         * @var array $lineItem
         */
        foreach ($cart->cart_contents as $lineItemKey => $lineItem) {
            /** @var WC_Product $product */
            $product = $lineItem["data"];
            if ($product->get_id() == $productId) {
                return new TaxModifierLineItemConnector($lineItemKey);
            }
        }
        throw new \OutOfBoundsException("Product ID {$productId} not matched in WC Cart");
    }
}
