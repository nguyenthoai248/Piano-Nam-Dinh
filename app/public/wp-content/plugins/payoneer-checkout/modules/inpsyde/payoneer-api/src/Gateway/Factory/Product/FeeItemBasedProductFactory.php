<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\Factory\Product;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\Product\QuantityNormalizerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use WC_Order_Item_Fee;
class FeeItemBasedProductFactory extends AbstractOrderItemBasedProductFactory implements FeeItemBasedProductFactoryInterface
{
    /**
     * @var ProductFactoryInterface
     */
    protected $productFactory;
    /**
     * @var QuantityNormalizerInterface
     */
    protected $quantityNormalizer;
    /**
     * @inheritDoc
     */
    public function createProduct(WC_Order_Item_Fee $feeItem, string $currency): ProductInterface
    {
        return $this->createProductFromShippingOrFeeItem($feeItem, $currency);
    }
}
