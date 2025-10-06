<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\Product;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use InvalidArgumentException;
use WC_Product;
/**
 * Service able to create a Payoneer SDK Product from WC_Product.
 */
interface WcBasedProductFactoryInterface
{
    /**
     * @param WC_Product $wcProduct
     * @param int|float|string $quantity
     * @param float $cartItemNetAmount
     * @param float $cartItemTaxAmount
     *
     * @return ProductInterface
     *
     * @throws InvalidArgumentException If failed to create product from provided data.
     */
    public function createProductFromWcProduct(WC_Product $wcProduct, $quantity, float $cartItemNetAmount, float $cartItemTaxAmount): ProductInterface;
}
