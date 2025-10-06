<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\Modifier;

class TaxModifierItem
{
    /** @var string $productId */
    private $productId;
    /** @var float $taxAmount */
    private $taxAmount;
    public function __construct(string $productId, float $taxAmount)
    {
        $this->productId = $productId;
        $this->taxAmount = $taxAmount;
    }
    public function getProductId() : string
    {
        return $this->productId;
    }
    public function setProductId(string $productId) : void
    {
        $this->productId = $productId;
    }
    public function getTaxAmount() : float
    {
        return $this->taxAmount;
    }
}
