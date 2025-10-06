<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\Authentication;

/**
 * A service able to generate a secure token.
 */
interface TokenGeneratorInterface
{
    /**
     * Generates a secure token.
     *
     * @return string
     */
    public function generateToken(): string;
}
