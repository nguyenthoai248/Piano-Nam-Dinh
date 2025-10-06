<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Client;

/**
 * A service providing authorization token.
 */
interface TokenAwareInterface
{
    /**
     * Return token.
     *
     * @return string A token string.
     */
    public function getToken(): string;
}
