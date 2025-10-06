<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutExceptionInterface;
/**
 * Should be thrown when failed to transform one entity into another.
 */
interface FactoryExceptionInterface extends CheckoutExceptionInterface
{
}
