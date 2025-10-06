<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\Factory\Product;

use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use WC_Cart;

/**
 * A service able to create product list from WC Cart.
 */
interface WcCartBasedProductListFactoryInterface
{
    /**
     * Create a product list from WC Cart.
     *
     * @param WC_Cart $cart Cart to create product list from.
     *
     * @return ProductInterface[] Products list.
     */
    public function createProductListFromWcCart(WC_Cart $cart): array;
}
