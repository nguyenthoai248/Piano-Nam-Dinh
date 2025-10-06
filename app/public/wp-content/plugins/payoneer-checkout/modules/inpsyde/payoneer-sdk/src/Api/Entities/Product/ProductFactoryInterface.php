<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product;

/**
 * A service able to create a new product instance.
 */
interface ProductFactoryInterface
{
    /**
     * Create a new product.
     * @param ProductType::* $type Product type (one of constants defined in the ProductType::class).
     * @param string $code Merchant-defined product code.
     * @param string $name Human-readable product name.
     * @param float $amount Price of product with respect to the quantity field.
     * @param string $currency Product currency.
     * @param int $quantity Number of products.
     * @param float $netAmount Product price without discounts and taxes.
     * @param float $taxAmount Amount of tax in the product price.
     * @param string|null $productDescriptionUrl URL of the product page.
     * @param string|null $productImageUrl URL of the product image.
     * @param string|null $description Product description in a free form (no markup supported).
     * @param string|null $taxCode Product tax code
     *
     * @return ProductInterface Created product.
     */
    public function createProduct(string $type, string $code, string $name, float $amount, string $currency, int $quantity, float $netAmount, float $taxAmount, string $productDescriptionUrl = null, string $productImageUrl = null, string $description = null, string $taxCode = null): ProductInterface;
}
