<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Client;

use Syde\Vendor\Inpsyde\PayoneerSdk\PayoneerSdkExceptionInterface;
/**
 * Should be thrown if an API request failed or an error code received as a response.
 */
interface ApiClientExceptionInterface extends PayoneerSdkExceptionInterface
{
    /**
     * Return the client thrown this exception.
     *
     * @return ApiClientInterface Instance of the client.
     */
    public function getClient(): ApiClientInterface;
}
