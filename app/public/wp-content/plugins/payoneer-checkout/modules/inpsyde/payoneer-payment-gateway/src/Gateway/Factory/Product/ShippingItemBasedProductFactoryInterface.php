<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Product;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use InvalidArgumentException;
use WC_Order_Item_Shipping;
interface ShippingItemBasedProductFactoryInterface
{
    /**
     * Create a product from WC order item shipping.
     *
     * @param WC_Order_Item_Shipping $shippingItem WC order item to get data from.
     * @param string $currency Shipping amount currency.
     *
     * @return ProductInterface Created product.
     *
     * @throws InvalidArgumentException If failed to create a product from provided order item.
     */
    public function createProduct(WC_Order_Item_Shipping $shippingItem, string $currency) : ProductInterface;
}
