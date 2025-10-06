<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Product;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\ProductTaxCodeProvider\ProductTaxCodeProviderInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\Product\QuantityNormalizerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductType;
use InvalidArgumentException;
use WC_Order;
use WC_Order_Item_Product;
use WC_Product;
class ProductItemBasedProductFactory extends AbstractOrderItemBasedProductFactory implements ProductItemBasedProductFactoryInterface
{
    /**
     * @var ProductFactoryInterface
     */
    protected $productFactory;
    /**
     * @var QuantityNormalizerInterface
     */
    protected $quantityNormalizer;
    /**
     * @var int
     */
    protected $priceDecimals;
    /**
     * @var ProductTaxCodeProviderInterface
     */
    protected $taxCodeProvider;
    /**
     * @param ProductFactoryInterface $productFactory
     * @param QuantityNormalizerInterface $quantityNormalizer
     * @param int $priceDecimals
     * @param ProductTaxCodeProviderInterface $taxCodeProvider
     */
    public function __construct(ProductFactoryInterface $productFactory, QuantityNormalizerInterface $quantityNormalizer, int $priceDecimals, ProductTaxCodeProviderInterface $taxCodeProvider)
    {
        parent::__construct($productFactory, $quantityNormalizer);
        $this->priceDecimals = $priceDecimals;
        $this->taxCodeProvider = $taxCodeProvider;
    }
    /**
     * @inheritDoc
     */
    public function createProduct(WC_Order_Item_Product $productItem, string $currency, WC_Order $order) : ProductInterface
    {
        $product = $productItem->get_product();
        if (!$product instanceof WC_Product) {
            throw new InvalidArgumentException('Cannot create product from provided order item, it contains no WC product.');
        }
        $description = $product->get_description();
        $type = $product->is_virtual() ? ProductType::DIGITAL : ProductType::PHYSICAL;
        $code = $product->get_id();
        $name = $productItem->get_name();
        $amount = (float) wc_format_decimal($productItem->get_total(), $this->priceDecimals) + (float) wc_format_decimal($productItem->get_total_tax(), $this->priceDecimals);
        /**
         * Some plugins change quantity to float.
         * The type of returned value is not restricted by WC.
         *
         * @var int|float|string $quantity
         */
        $quantity = $productItem->get_quantity();
        $quantity = $this->quantityNormalizer->normalizeQuantity($quantity);
        /**
         * @var int|float|string $itemTax
         */
        $itemTax = $order->get_item_tax($productItem, \false);
        $taxAmount = (float) $itemTax * (float) $productItem->get_quantity();
        $netAmount = $amount - $taxAmount;
        $productDescriptionUrl = $product->get_permalink();
        $imageId = $product->get_image_id();
        $imageData = wp_get_attachment_image_src((int) $imageId, 'woocommerce_single', \true);
        $productImageUrl = $imageData[0] ?? '';
        return $this->productFactory->createProduct($type, (string) $code, $name, $amount, $currency, $quantity, $netAmount, $taxAmount, $productDescriptionUrl, $productImageUrl, wp_strip_all_tags(htmlspecialchars_decode($description)), $this->taxCodeProvider->provideProductTaxCode($product));
    }
}
