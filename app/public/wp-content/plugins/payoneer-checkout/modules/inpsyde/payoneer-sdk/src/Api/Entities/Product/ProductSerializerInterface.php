<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product;

/**
 * Service able to convert Product instance to array.
 */
interface ProductSerializerInterface
{
    /**
     * Return map of product fields.
     *
     * @param ProductInterface $product
     *
     * @return array {
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
     * }
     */
    public function serializeProduct(ProductInterface $product): array;
}
