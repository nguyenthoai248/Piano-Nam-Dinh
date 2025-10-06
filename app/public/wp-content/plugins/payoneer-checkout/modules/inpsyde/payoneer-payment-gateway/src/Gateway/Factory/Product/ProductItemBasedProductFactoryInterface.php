<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Product;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use InvalidArgumentException;
use WC_Order;
use WC_Order_Item_Product;
interface ProductItemBasedProductFactoryInterface
{
    /**
     * Create a product from WC order item product.
     *
     * @param WC_Order_Item_Product $productItem WC order item to get data from.
     *
     * @return ProductInterface Created product.
     *
     * @throws InvalidArgumentException If product cannot be created from the provided item.
     */
    public function createProduct(WC_Order_Item_Product $productItem, string $currency, WC_Order $order) : ProductInterface;
}
