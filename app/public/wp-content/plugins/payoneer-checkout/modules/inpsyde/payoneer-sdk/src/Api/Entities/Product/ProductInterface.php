<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
/**
 * Represents one or few identical shop products.
 * The number of products must be reflected in the quantity field.
 * The amount field must be set with respect to the quantity field (so if one product costs
 * 10.00 and quantity is 2, then amount must be 20.00).
 */
interface ProductInterface
{
    /**
     * Return merchant's product identifier.
     *
     * @return string Product ID
     */
    public function getCode(): string;
    /**
     * Return product name.
     *
     * @return string Human-readable product name.
     */
    public function getName(): string;
    /**
     * Return the price of product(s) with respect to the quantity field.
     *
     * @return float The price of the product (or products if quantity more than 1).
     */
    public function getAmount(): float;
    /**
     * @return float
     */
    public function getNetAmount(): float;
    /**
     * Return the tax amount in the product price.
     *
     * @return float The tax amount in the product price
     */
    public function getTaxAmount(): float;
    /**
     * Return code of the product currency.
     *
     * @return string Currency code.
     */
    public function getCurrency(): string;
    /**
     * Return number of product items.
     *
     * @return int Products number.
     */
    public function getQuantity(): int;
    /**
     * Return URL of the product description page.
     *
     * @return string Product URL.
     *
     * @throws ApiExceptionInterface If this field is not set.
     */
    public function getProductDescriptionUrl(): string;
    /**
     * Return URL of the product image.
     *
     * @return string Product image URL.
     *
     * @throws ApiExceptionInterface If this field is not set.
     */
    public function getProductImageUrl(): string;
    /**
     * Return product description (without any markup).
     *
     * @return string Product description.
     *
     * @throws ApiExceptionInterface If this field is not set.
     */
    public function getDescription(): string;
    /**
     * Return product type (one of PHYSICAL, DIGITAL, SERVICE, TAX and OTHER).
     *
     * @return ProductType::* Product type.
     */
    public function getType(): string;
    /**
     * Return product tax code.
     *
     * @return string
     *
     * @throws ApiExceptionInterface If this field is not set.
     */
    public function getTaxCode(): string;
}
