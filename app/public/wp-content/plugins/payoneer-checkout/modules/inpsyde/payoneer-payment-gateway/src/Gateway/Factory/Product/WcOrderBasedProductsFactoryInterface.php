<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Product;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use WC_Order;
interface WcOrderBasedProductsFactoryInterface
{
    /**
     * Create a products of a WC Order instance.
     *
     * @return ProductInterface[]
     */
    public function createProductsFromWcOrder(WC_Order $order) : array;
}
