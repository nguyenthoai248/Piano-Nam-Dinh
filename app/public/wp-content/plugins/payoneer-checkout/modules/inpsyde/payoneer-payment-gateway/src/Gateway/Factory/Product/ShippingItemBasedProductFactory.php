<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Product;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\Product\QuantityNormalizerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use WC_Order_Item_Shipping;
class ShippingItemBasedProductFactory extends AbstractOrderItemBasedProductFactory implements ShippingItemBasedProductFactoryInterface
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
    public function createProduct(WC_Order_Item_Shipping $shippingItem, string $currency) : ProductInterface
    {
        return $this->createProductFromShippingOrFeeItem($shippingItem, $currency);
    }
}
