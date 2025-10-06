<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Api;

use Syde\Vendor\Inpsyde\PayoneerSdk\Client\TokenAwareInterface;
/**
 * Can create a token provider from a username and a password.
 */
class BasicTokenProviderFactory implements BasicTokenProviderFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createBasicProvider(string $username, string $password) : TokenAwareInterface
    {
        $product = new BasicTokenProvider($username, $password);
        return $product;
    }
}
