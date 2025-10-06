<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiException;
class ProductDeserializer implements ProductDeserializerInterface
{
    /**
     * @var ProductFactoryInterface
     */
    protected $productFactory;
    /**
     * @param ProductFactoryInterface $productFactory To create a product instance.
     */
    public function __construct(ProductFactoryInterface $productFactory)
    {
        $this->productFactory = $productFactory;
    }
    /**
     * @inheritDoc
     */
    public function deserializeProduct(array $productData): ProductInterface
    {
        if (!isset($productData['type'])) {
            throw new ApiException('Data contains no expected type element');
        }
        $type = $productData['type'];
        if (!isset($productData['code'])) {
            throw new ApiException('Data contains no expected code element');
        }
        $code = $productData['code'];
        if (!isset($productData['name'])) {
            throw new ApiException('Data contains no expected name element');
        }
        $name = $productData['name'];
        if (!isset($productData['amount'])) {
            throw new ApiException('Data contains no expected amount element');
        }
        $amount = $productData['amount'];
        if (!isset($productData['currency'])) {
            throw new ApiException('Data contains no expected currency element');
        }
        $currency = $productData['currency'];
        if (!isset($productData['quantity'])) {
            throw new ApiException('Data contains no expected quantity element');
        }
        $quantity = $productData['quantity'];
        $netAmount = $productData['netAmount'];
        $taxAmount = $productData['taxAmount'] ?? 0.0;
        $productDescriptionUrl = $productData['productDescriptionUrl'] ?? null;
        $productImageUrl = $productData['productImageUrl'] ?? null;
        $description = $productData['description'] ?? null;
        $taxCode = $productData['taxCode'] ?? null;
        return $this->productFactory->createProduct($type, $code, $name, $amount, $currency, $quantity, $netAmount, $taxAmount, $productDescriptionUrl, $productImageUrl, $description, $taxCode);
    }
}
