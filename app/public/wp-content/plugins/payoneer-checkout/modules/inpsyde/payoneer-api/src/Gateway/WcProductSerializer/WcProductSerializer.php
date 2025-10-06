<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\WcProductSerializer;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductType;
use WC_Product;
class WcProductSerializer implements WcProductSerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serializeWcProduct(WC_Product $product): array
    {
        $code = (string) $product->get_id();
        $name = $product->get_title();
        $amount = (float) $product->get_price('');
        $currency = get_woocommerce_currency();
        $descriptionUrl = $product->get_permalink();
        $imageId = $product->get_image_id();
        $imageData = wp_get_attachment_image_src((int) $imageId, 'woocommerce_single', \true);
        $imageUrl = $imageData[0] ?? '';
        $description = $product->get_description();
        $type = $product->is_virtual() ? ProductType::DIGITAL : ProductType::PHYSICAL;
        /** @psalm-suppress TooManyArguments */
        return ['code' => $code, 'name' => $name, 'amount' => $amount, 'currency' => $currency, 'quantity' => 1, 'productDescriptionUrl' => $descriptionUrl, 'productImageUrl' => $imageUrl, 'description' => $description, 'type' => (string) apply_filters('payoneer-checkout.product_type', $type, $product)];
    }
}
