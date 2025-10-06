<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\Authentication;

class TokenGenerator implements TokenGeneratorInterface
{
    /**
     * @inheritDoc
     */
    public function generateToken(): string
    {
        return wp_generate_password(32, \true);
    }
}
