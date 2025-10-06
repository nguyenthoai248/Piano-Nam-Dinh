<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\ProductTaxCodeProvider;

interface ProductTaxCodeProviderInterface
{
    /**
     * Returns tax code for product.
     *
     * @param \WC_Product $product
     *
     * @return string|null
     */
    public function provideProductTaxCode(\WC_Product $product): ?string;
}
