<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Product;

use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductType;
use WC_Order;
use WC_Order_Item_Product;

/**
 * Creates separate Product from WC product discount.
 *
 * When product item price changed manually (for example, order is created in WC admin,
 * product added, and its price reduced manually on the order page), WooCommerce displays this as a
 * discount. On the order-pay page this looks similar to coupon: as a separate line item called
 * Discount, although no actual coupon is created. There is no object representing this entity in
 * WooCommerce, it is just some logic located directly in the template file
 * wp-content/plugins/woocommerce/includes/admin/meta-boxes/views/html-order-item.php
 *
 * But we need to explain to Payoneer API there IS a discount. So, we create entity almost same as
 * we create for the actual coupon. This way, it can be reflected in the merchant account.
 *
 */
class DiscountedProductItemBasedProductFactory implements ProductItemBasedProductFactoryInterface
{
    /**
     * @var ProductFactoryInterface
     */
    protected $productFactory;

    /**
     * @param ProductFactoryInterface $productFactory
     */
    public function __construct(ProductFactoryInterface $productFactory)
    {

        $this->productFactory = $productFactory;
    }

    /**
     * @inheritDoc
     */
    public function createProduct(
        WC_Order_Item_Product $productItem,
        string $currency,
        WC_Order $order
    ): ProductInterface {

        $type = ProductType::DIGITAL;
        $discountFor = esc_html__('Discount for', 'payoneer-checkout');
        $code = (string) $productItem->get_product_id() . '-discount';
        $name = sprintf('%1$s %2$s', $discountFor, $productItem->get_name());
        $subtotal = $productItem->get_subtotal();
        $total = $productItem->get_total();
        $amount = ((float)$subtotal - (float) $total) * -1.0;
        $quantity = 1;
        $netAmount = $amount;
        $taxAmount = 0.0; //No taxes for product discount.

        return $this->productFactory->createProduct(
            $type,
            $code,
            $name,
            $amount,
            $currency,
            $quantity,
            $netAmount,
            $taxAmount
        );
    }
}
