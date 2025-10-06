<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Api;

use Syde\Vendor\Inpsyde\PayoneerSdk\Client\TokenAwareInterface;
use RuntimeException;
/**
 * Something that can create a token provider from a username and a password.
 */
interface BasicTokenProviderFactoryInterface
{
    /**
     * Creates a token provider from a username and a password.
     *
     * @param string $username The username.
     * @param string $password The password.
     *
     * @return TokenAwareInterface The new provider.
     *
     * @throws RuntimeException If problem creating.
     */
    public function createBasicProvider(string $username, string $password): TokenAwareInterface;
}
