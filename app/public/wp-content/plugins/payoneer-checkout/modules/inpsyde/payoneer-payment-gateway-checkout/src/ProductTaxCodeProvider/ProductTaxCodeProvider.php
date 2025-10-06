<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\ProductTaxCodeProvider;

class ProductTaxCodeProvider implements ProductTaxCodeProviderInterface
{
    /**
     * @var string
     */
    protected $taxCodeFieldName;
    /**
     * @var string|null
     */
    protected $defaultTaxCode;
    /**
     * @param string $taxCodeFieldName
     * @param string|null $defaultTaxCode
     */
    public function __construct(string $taxCodeFieldName, string $defaultTaxCode = null)
    {
        $this->taxCodeFieldName = $taxCodeFieldName;
        $this->defaultTaxCode = $defaultTaxCode;
    }
    /**
     * @inheritDoc
     */
    public function provideProductTaxCode(\WC_Product $product): ?string
    {
        if ($product->meta_exists($this->taxCodeFieldName)) {
            return (string) $product->get_meta($this->taxCodeFieldName);
        }
        $parentId = $product->get_parent_id();
        $parentProduct = wc_get_product($parentId);
        if (!$parentProduct instanceof \WC_Product) {
            return $this->defaultTaxCode;
        }
        return $parentProduct->meta_exists($this->taxCodeFieldName) ? (string) $parentProduct->get_meta($this->taxCodeFieldName) : $this->defaultTaxCode;
    }
}
