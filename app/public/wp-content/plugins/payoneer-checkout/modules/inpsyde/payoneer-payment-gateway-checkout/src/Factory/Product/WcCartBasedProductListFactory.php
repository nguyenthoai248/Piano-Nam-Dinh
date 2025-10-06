<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\Factory\Product;

use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductType;
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
    public function __construct(
        WcBasedProductFactoryInterface $wcProductBasedProductFactory,
        ProductFactoryInterface $productFactory,
        string $currency
    ) {

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

        /** @var mixed $discount */
        $discount = $cart->get_discount_total();

        if ((float) $discount > 0.0) {
            $products[] = $this->createDiscountProduct((float)$discount  * -1.0);
        }

        /** @var mixed $feeTotal */
        $feeTotal = $cart->get_fee_total();
        if ((float) $feeTotal > 0.0) {
            foreach ($cart->get_fees() as $fee) {
                /** @var StdClass $fee */
                $products[] = $this->createProductFromCartFee($fee);
            }
        }

        /** @var mixed $shippingTotal */
        $shippingTotal = $cart->get_shipping_total();
        /** @var mixed $shippingTax */
        $shippingTax = $cart->get_shipping_tax();

        if ((float) $shippingTotal > 0.0) {
            $products[] = $this->createShippingProduct(
                (float) $shippingTotal,
                (float) $shippingTax
            );
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
        return $this->wcProductBasedProductFactory->createProductFromWcProduct(
            $cartItem['data'],
            $cartItem['quantity'],
            $cartItem['line_subtotal'],
            (float) reset($cartItem['line_tax_data']['total'])
        );
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
        $total = (float) $fee->total;
        $tax = (float) $fee->tax;

        return $this->productFactory->createProduct(
            ProductType::DIGITAL,
            (string) $fee->id,
            (string) $fee->name,
            $total,
            $this->currency,
            1,
            $total - $tax,
            $tax
        );
    }

    /**
     * Create shipping product from cart shipping.
     *
     * @param float $amount
     * @param float $tax
     *
     * @return ProductInterface
     */
    protected function createShippingProduct(float $amount, float $tax): ProductInterface
    {

        return $this->productFactory->createProduct(
            ProductType::SERVICE,
            'shipping-services',
            esc_html__('Shipping', 'payoneer-checkout'),
            $amount,
            $this->currency,
            1,
            $amount - $tax,
            $tax
        );
    }

    /**
     * Create a product representing cart discount.
     *
     * @param float $amount
     *
     * @return ProductInterface
     */
    protected function createDiscountProduct(float $amount): ProductInterface
    {
        return $this->productFactory->createProduct(
            ProductType::DIGITAL,
            'cart-discount',
            esc_html__('Cart discount', 'payoneer-checkout'),
            $amount,
            $this->currency,
            1,
            $amount,
            0
        );
    }
}
