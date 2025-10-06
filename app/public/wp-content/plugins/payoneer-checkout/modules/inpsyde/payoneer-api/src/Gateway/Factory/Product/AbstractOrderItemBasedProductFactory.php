<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\Factory\Product;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\Product\QuantityNormalizerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductType;
use InvalidArgumentException;
use WC_Order_Item;
use WC_Order_Item_Fee;
use WC_Order_Item_Shipping;
abstract class AbstractOrderItemBasedProductFactory
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
     * @param ProductFactoryInterface $productFactory
     * @param QuantityNormalizerInterface $quantityNormalizer
     */
    public function __construct(ProductFactoryInterface $productFactory, QuantityNormalizerInterface $quantityNormalizer)
    {
        $this->productFactory = $productFactory;
        $this->quantityNormalizer = $quantityNormalizer;
    }
    /**
     * @param WC_Order_Item_Shipping|WC_Order_Item_Fee $item
     * @param string $currency
     *
     * @return ProductInterface
     *
     * @throws InvalidArgumentException If improper item subtype provided.
     */
    protected function createProductFromShippingOrFeeItem(WC_Order_Item $item, string $currency): ProductInterface
    {
        if (!$item instanceof WC_Order_Item_Shipping && !$item instanceof WC_Order_Item_Fee) {
            throw new InvalidArgumentException('Unexpected type of order item provided, expected Shipping or Fee.');
        }
        $type = $item instanceof WC_Order_Item_Shipping ? ProductType::SERVICE : ProductType::OTHER;
        $code = (string) $item->get_id();
        $name = $item->get_name();
        $taxAmount = (float) $item->get_total_tax();
        $netAmount = (float) $item->get_total();
        $amount = $netAmount + $taxAmount;
        /**
         * @var int|float|string $quantity
         */
        $quantity = $item->get_quantity();
        $quantity = $this->quantityNormalizer->normalizeQuantity($quantity);
        return $this->productFactory->createProduct($type, $code, $name, $amount, $currency, $quantity, $netAmount, $taxAmount);
    }
}
