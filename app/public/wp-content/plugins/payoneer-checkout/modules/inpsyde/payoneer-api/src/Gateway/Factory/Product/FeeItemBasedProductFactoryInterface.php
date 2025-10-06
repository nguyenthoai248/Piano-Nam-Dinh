<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\Factory\Product;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use InvalidArgumentException;
use WC_Order_Item_Fee;
interface FeeItemBasedProductFactoryInterface
{
    /**
     * Create product from order item fee.
     *
     * @param WC_Order_Item_Fee $feeItem WC order item to get data from.
     * @param string $currency Fee currency.
     *
     * @return ProductInterface Created product.
     *
     * @throws InvalidArgumentException If failed to create a product from provided item.
     */
    public function createProduct(WC_Order_Item_Fee $feeItem, string $currency): ProductInterface;
}
