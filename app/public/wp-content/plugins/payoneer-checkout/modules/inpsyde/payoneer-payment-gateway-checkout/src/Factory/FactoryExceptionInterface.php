<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\Factory;

use Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutExceptionInterface;

/**
 * Should be thrown when failed to transform one entity into another.
 */
interface FactoryExceptionInterface extends CheckoutExceptionInterface
{
}
