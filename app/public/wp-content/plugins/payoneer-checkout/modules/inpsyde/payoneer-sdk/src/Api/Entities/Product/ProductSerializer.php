<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
class ProductSerializer implements ProductSerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serializeProduct(ProductInterface $product): array
    {
        $serializedProduct = ['type' => $product->getType(), 'code' => $product->getCode(), 'name' => $product->getName(), 'amount' => $product->getAmount(), 'currency' => $product->getCurrency(), 'quantity' => $product->getQuantity(), 'taxAmount' => $product->getTaxAmount(), 'netAmount' => $product->getNetAmount()];
        try {
            $serializedProduct['productDescriptionUrl'] = $product->getProductDescriptionUrl();
        } catch (ApiExceptionInterface $apiException) {
            //this is an optional field, so it's ok to not have it
        }
        try {
            $serializedProduct['productImageUrl'] = $product->getProductImageUrl();
        } catch (ApiExceptionInterface $apiException) {
            //this is an optional field, so it's ok to not have it
        }
        try {
            $serializedProduct['description'] = $product->getDescription();
        } catch (ApiExceptionInterface $apiException) {
            //this is an optional field, so it's ok to not have it
        }
        try {
            $serializedProduct['taxCode'] = $product->getTaxCode();
        } catch (ApiExceptionInterface $apiException) {
            //this is an optional field, so it's ok to not have it
        }
        return $serializedProduct;
    }
}
