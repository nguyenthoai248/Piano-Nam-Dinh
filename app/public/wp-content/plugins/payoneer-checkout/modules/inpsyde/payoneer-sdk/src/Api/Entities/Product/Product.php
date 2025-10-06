<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiException;
class Product implements ProductInterface
{
    /**
     * @var string
     */
    protected $code;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var float
     */
    protected $amount;
    /**
     * @var string
     */
    protected $currency;
    /**
     * @var int
     */
    protected $quantity;
    /**
     * @var string|null
     */
    protected $productDescriptionUrl;
    /**
     * @var string|null
     */
    protected $productImageUrl;
    /**
     * @var string|null
     */
    protected $description;
    /**
     * @var ProductType::*
     */
    protected $type;
    /**
     * @var float
     */
    protected $taxAmount;
    /**
     * @var float
     */
    protected $netAmount;
    /**
     * @var string|null
     */
    protected $taxCode;
    /**
     * @param ProductType::* $productType Product type (one of constants defined in the ProductType::class).
     * @param string $code Merchant-defined product code.
     * @param string $name Human-readable product name.
     * @param float $amount Price of product with respect to the quantity field.
     * @param string $currency Product currency.
     * @param int $quantity Number of products.
     * @param float $netAmount The price of the product without discounts and taxes.
     * @param float $taxAmount The amount of tax in the product price.
     * @param string|null $productDescriptionUrl URL of the product page.
     * @param string|null $productImageUrl URL of the product image.
     * @param string|null $description Product description in a free form (no markup supported).
     * @param string|null $taxCode Product tax code.
     */
    public function __construct(string $type, string $code, string $name, float $amount, string $currency, int $quantity, float $netAmount = null, float $taxAmount = 0.0, string $productDescriptionUrl = null, string $productImageUrl = null, string $description = null, string $taxCode = null)
    {
        $this->code = $code;
        $this->name = $name;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->productDescriptionUrl = $productDescriptionUrl;
        $this->productImageUrl = $productImageUrl;
        $this->description = $description;
        /** @var ProductType::* $type */
        $this->type = $type;
        $this->quantity = $quantity;
        $this->taxAmount = $taxAmount;
        $this->netAmount = $netAmount ?? $amount;
        $this->taxCode = $taxCode;
    }
    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return $this->code;
    }
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }
    /**
     * @inheritDoc
     */
    public function getAmount(): float
    {
        return $this->amount;
    }
    public function getNetAmount(): float
    {
        return $this->netAmount;
    }
    public function getTaxAmount(): float
    {
        return $this->taxAmount;
    }
    /**
     * @inheritDoc
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }
    public function getQuantity(): int
    {
        return $this->quantity;
    }
    /**
     * @inheritDoc
     */
    public function getProductDescriptionUrl(): string
    {
        if ($this->productDescriptionUrl === null) {
            throw new ApiException('productDescriptionUrl field is not set');
        }
        return $this->productDescriptionUrl;
    }
    /**
     * @inheritDoc
     */
    public function getProductImageUrl(): string
    {
        if ($this->productImageUrl === null) {
            throw new ApiException('productImageUrl field is not set');
        }
        return $this->productImageUrl;
    }
    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        if ($this->description === null) {
            throw new ApiException('description field is not set');
        }
        return $this->description;
    }
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->type;
    }
    /**
     * @inheritDoc
     */
    public function getTaxCode(): string
    {
        if ($this->taxCode === null) {
            throw new ApiException('taxCode field is not set');
        }
        return $this->taxCode;
    }
}
