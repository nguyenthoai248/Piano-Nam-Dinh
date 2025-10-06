<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
/**
 * Service able to create a product instance from an array with product data.
 */
interface ProductDeserializerInterface
{
    /**
     * @param array {
     *     type: ProductType::*,
     *     code: string,
     *     name: string,
     *     amount: float,
     *     currency: string,
     *     quantity: int,
     *     netAmount: float,
     *     taxAmount: float
     *     productDescriptionUrl?: string,
     *     productImageUrl?: string,
     *     description?: string,
     *     taxCode?: string
     * } $productData
     *
     * @return ProductInterface
     *
     * @throws ApiExceptionInterface If failed to deserialize product.
     */
    public function deserializeProduct(array $productData): ProductInterface;
}
