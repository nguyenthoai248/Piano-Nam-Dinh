<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\WcProductSerializer;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductType;
use WC_Product;
/**
 * Service able to create a map of WC_Product fields.
 */
interface WcProductSerializerInterface
{
    /**
     * @return array{
     *     code: string,
     *     name: string,
     *     amount: float,
     *     currency: string,
     *     quantity: int,
     *     productDescriptionUrl: string,
     *     productImageUrl: string,
     *     description: string,
     *     type: ProductType::*,
     * }
     */
    public function serializeWcProduct(WC_Product $product): array;
}
