<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\Factory\Product;

use InvalidArgumentException;

interface QuantityNormalizerInterface
{
    /**
     *
     * Convert provided quantity value to int with the same numeric amount.
     *
     * Normally WC quantity is int, but some plugins change it to float and there
     * is no guarantee of type.
     *
     * @param int|float|string $quantity
     *
     * @return int
     *
     * @throws InvalidArgumentException If provided value cannot be properly normalized.
     */
    public function normalizeQuantity($quantity): int;
}
