<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\Factory\Product;

use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\WcProductSerializer\WcProductSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use WC_Product;

/**
 * @psalm-type CartItem=array{
 *     key: string,
 *     product_id: int,
 *     variation_id: int,
 *     variation: array,
 *     quantity: int,
 *     data_hash: string,
 *     line_tax_data: array,
 *     line_subtotal: float,
 *     line_subtotal: float,
 *     line_total: float,
 *     line_tax: float,
 *     data: \WC_Product,
 *     data_store: \WC_Data_Store,
 *     meta_data: array|null
 * }
 */
class WcBasedProductFactory implements WcBasedProductFactoryInterface
{
    /**
     * @var ProductFactoryInterface
     */
    protected $productFactory;
    /**
     * @var string
     */
    protected $currency;
    /**
     * @var WcProductSerializerInterface
     */
    protected $wcProductSerializer;
    /**
     * @var QuantityNormalizerInterface
     */
    protected $quantityNormalizer;

    /**
     * @param WcProductSerializerInterface $wcProductSerializer
     * @param ProductFactoryInterface $productFactory
     * @param QuantityNormalizerInterface $quantityNormalizer
     * @param string $currency
     */
    public function __construct(
        WcProductSerializerInterface $wcProductSerializer,
        ProductFactoryInterface $productFactory,
        QuantityNormalizerInterface $quantityNormalizer,
        string $currency
    ) {

        $this->wcProductSerializer = $wcProductSerializer;
        $this->productFactory = $productFactory;
        $this->currency = $currency;
        $this->quantityNormalizer = $quantityNormalizer;
    }

    /**
     * @inheritDoc
     */
    public function createProductFromWcProduct(
        WC_Product $wcProduct,
        $quantity,
        float $cartItemNetAmount,
        float $cartItemTaxAmount
    ): ProductInterface {

        $serializedProduct = $this->wcProductSerializer->serializeWcProduct($wcProduct);

        //Payoneer API requires quantity to be integer, so there is no way to support floats.
        $quantity = $this->quantityNormalizer->normalizeQuantity($quantity);
        $amount = $cartItemNetAmount + $cartItemTaxAmount;

        return $this->productFactory->createProduct(
            $serializedProduct['type'],
            $serializedProduct['code'],
            $serializedProduct['name'],
            $amount,
            $this->currency,
            $quantity,
            $cartItemNetAmount,
            $cartItemTaxAmount,
            $serializedProduct['productDescriptionUrl'],
            $serializedProduct['productImageUrl'],
            $serializedProduct['description']
        );
    }
}
