<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\Product;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductType;
use stdClass;
use WC_Cart;
/**
 * @psalm-type CartItem=array{
 *     key: string,
 *     product_id: int,
 *     variation_id: int,
 *     variation: array,
 *     quantity: int,
 *     data_hash: string,
 *     line_tax_data: array,
 *     line_subtotal: float,
 *     line_subtotal: float,
 *     line_total: float,
 *     line_tax: float,
 *     data: \WC_Product,
 *     data_store: \WC_Data_Store,
 *     meta_data: array|null
 * }
 */
class WcCartBasedProductListFactory implements WcCartBasedProductListFactoryInterface
{
    const SHIPPING_SERVICES_TYPE = 'shipping-services';
    public const DISCOUNT_TYPE = 'cart-discount';
    protected $wcProductBasedProductFactory;
    /**
     * @var ProductFactoryInterface
     */
    protected $productFactory;
    /**
     * @var string
     */
    protected $currency;
    /**
     * @param WcBasedProductFactoryInterface $wcProductBasedProductFactory
     * @param ProductFactoryInterface $productFactory
     * @param string $currency
     */
    public function __construct(WcBasedProductFactoryInterface $wcProductBasedProductFactory, ProductFactoryInterface $productFactory, string $currency)
    {
        $this->wcProductBasedProductFactory = $wcProductBasedProductFactory;
        $this->productFactory = $productFactory;
        $this->currency = $currency;
    }
    /**
     * @inheritDoc
     */
    public function createProductListFromWcCart(WC_Cart $cart): array
    {
        /**
         * @var array<CartItem> $cartItems
         */
        $cartItems = $cart->get_cart();
        $products = [];
        foreach ($cartItems as $cartItem) {
            $products[] = $this->createProductFromCartItem($cartItem);
        }
        /** @var mixed $feeTotal */
        $feeTotal = $cart->get_fee_total();
        if ((float) $feeTotal > 0.0) {
            foreach ($cart->get_fees() as $fee) {
                /** @var StdClass $fee */
                $products[] = $this->createProductFromCartFee($fee);
            }
        }
        /** @var mixed $shippingNet */
        $shippingNet = $cart->get_shipping_total();
        /** @var mixed $shippingTax */
        $shippingTax = $cart->get_shipping_tax();
        if ((float) $shippingNet > 0.0) {
            $products[] = $this->createShippingProduct((float) $shippingNet, (float) $shippingTax);
        }
        return $products;
    }
    /**
     * @psalm-param CartItem $cartItem
     * @param array $cartItem
     *
     * @return ProductInterface
     *
     * @psalm-suppress MixedArgument
     */
    protected function createProductFromCartItem(array $cartItem): ProductInterface
    {
        /**
         * @psalm-suppress RedundantCastGivenDocblockType
         */
        return $this->wcProductBasedProductFactory->createProductFromWcProduct($cartItem['data'], $cartItem['quantity'], $cartItem['line_total'], (float) reset($cartItem['line_tax_data']['total']));
    }
    /**
     * Create product from cart fee.
     *
     * @param stdClass $fee
     *
     * @return ProductInterface
     */
    protected function createProductFromCartFee(StdClass $fee): ProductInterface
    {
        $net = (float) $fee->total;
        $tax = (float) $fee->tax;
        return $this->productFactory->createProduct(ProductType::OTHER, (string) $fee->id, (string) $fee->name, $net + $tax, $this->currency, 1, $net, $tax);
    }
    /**
     * Create shipping product from cart shipping.
     *
     * @param float $netAmount
     * @param float $taxAmount
     *
     * @return ProductInterface
     */
    protected function createShippingProduct(float $netAmount, float $taxAmount): ProductInterface
    {
        return $this->productFactory->createProduct(ProductType::SERVICE, 'shipping-services', esc_html__('Shipping', 'payoneer-checkout'), $netAmount + $taxAmount, $this->currency, 1, $netAmount, $taxAmount);
    }
}
