<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Client;

use Syde\Vendor\Inpsyde\PayoneerSdk\Client\ApiClientInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\TokenAwareInterface;
use Syde\Vendor\Psr\Http\Message\UriInterface;
use RuntimeException;
/**
 * Something that can create an API client.
 */
interface ClientFactoryInterface
{
    /**
     * Creates an API client.
     *
     * @param UriInterface $baseUrl The base URL of the API.
     * @param TokenAwareInterface $tokenProvider The auth token provider.
     *
     * @return ApiClientInterface The new client.
     *
     * @throws RuntimeException If problem creating.
     */
    public function createClientForApi(UriInterface $baseUrl, TokenAwareInterface $tokenProvider) : ApiClientInterface;
}
