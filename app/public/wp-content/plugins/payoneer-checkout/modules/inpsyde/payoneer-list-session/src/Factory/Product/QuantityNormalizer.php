<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\Product;

use InvalidArgumentException;
class QuantityNormalizer implements QuantityNormalizerInterface
{
    /**
     * @inheritDoc
     */
    public function normalizeQuantity($quantity): int
    {
        $normalizedQuantity = (int) $quantity;
        //Non-strict comparison here is intentional.
        //We want true when comparing 1 and '1', but false when comparing 1 and '1.1'.
        if (is_numeric($quantity) && $normalizedQuantity == $quantity) {
            return $normalizedQuantity;
        }
        throw new InvalidArgumentException('Provided quantity value cannot be normalized.');
    }
}
