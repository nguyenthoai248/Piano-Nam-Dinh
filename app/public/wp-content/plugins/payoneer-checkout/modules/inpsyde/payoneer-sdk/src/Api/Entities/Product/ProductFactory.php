<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product;

class ProductFactory implements ProductFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createProduct(string $type, string $code, string $name, float $amount, string $currency, int $quantity, float $netAmount, float $taxAmount, string $productDescriptionUrl = null, string $productImageUrl = null, string $description = null, string $taxCode = null): ProductInterface
    {
        return new Product($type, $code, $name, $amount, $currency, $quantity, $netAmount, $taxAmount, $productDescriptionUrl, $productImageUrl, $description, $taxCode);
    }
}
