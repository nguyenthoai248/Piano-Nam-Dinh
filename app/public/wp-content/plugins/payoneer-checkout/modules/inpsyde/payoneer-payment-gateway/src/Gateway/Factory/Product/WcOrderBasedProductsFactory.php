<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Product;

use InvalidArgumentException;
use WC_Order;
use WC_Order_Item_Coupon;
use WC_Order_Item_Fee;
use WC_Order_Item_Product;
use WC_Order_Item_Shipping;
class WcOrderBasedProductsFactory implements WcOrderBasedProductsFactoryInterface
{
    /**
     * @var ProductItemBasedProductFactoryInterface
     */
    protected $productItemBasedProductFactory;
    /**
     * @var ShippingItemBasedProductFactoryInterface
     */
    protected $shippingBasedProductFactory;
    /**
     * @var FeeItemBasedProductFactoryInterface
     */
    protected $feeBasedProductFactory;
    /**
     * @var string[]
     */
    protected $orderItemTypes;
    /**
     * @param ProductItemBasedProductFactoryInterface $productBasedProductFactory
     * @param ShippingItemBasedProductFactoryInterface $shippingBasedProductFactory
     * @param FeeItemBasedProductFactoryInterface $feeBasedProductFactory
     * @param ProductItemBasedProductFactoryInterface $discountedProductItemBasedFactory
     * @param string[] $orderItemTypes Possible order item classes
     */
    public function __construct(ProductItemBasedProductFactoryInterface $productBasedProductFactory, ShippingItemBasedProductFactoryInterface $shippingBasedProductFactory, FeeItemBasedProductFactoryInterface $feeBasedProductFactory, array $orderItemTypes)
    {
        $this->productItemBasedProductFactory = $productBasedProductFactory;
        $this->shippingBasedProductFactory = $shippingBasedProductFactory;
        $this->feeBasedProductFactory = $feeBasedProductFactory;
        $this->orderItemTypes = $orderItemTypes;
    }
    /**
     * @inheritDoc
     */
    public function createProductsFromWcOrder(WC_Order $order) : array
    {
        $items = $order->get_items($this->orderItemTypes);
        $currency = $order->get_currency();
        $products = [];
        foreach ($items as $item) {
            switch (\true) {
                case $item instanceof WC_Order_Item_Product:
                    $products[] = $this->productItemBasedProductFactory->createProduct($item, $currency, $order);
                    break;
                case $item instanceof WC_Order_Item_Shipping:
                    $products[] = $this->shippingBasedProductFactory->createProduct($item, $currency);
                    break;
                case $item instanceof WC_Order_Item_Fee:
                    $products[] = $this->feeBasedProductFactory->createProduct($item, $currency);
                    break;
                case $item instanceof WC_Order_Item_Coupon:
                    //No special action about coupon order item, we process discount separately at
                    //the order items level.
                    break;
                default:
                    $allowedTypes = implode(', ', $this->orderItemTypes);
                    $actualType = get_class($item);
                    throw new InvalidArgumentException(sprintf('Failed to create product from WC order item. Expected one of %1$s, %2$s provided.', $allowedTypes, $actualType));
            }
        }
        return $products;
    }
}
