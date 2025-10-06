<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Client;

use Syde\Vendor\Inpsyde\PayoneerSdk\Client\ApiClient;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\ApiClientInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\TokenAwareInterface;
use Syde\Vendor\Psr\Http\Client\ClientInterface;
use Syde\Vendor\Psr\Http\Message\RequestFactoryInterface;
use Syde\Vendor\Psr\Http\Message\StreamFactoryInterface;
use Syde\Vendor\Psr\Http\Message\UriInterface;
/**
 * Can create a client for an API.
 */
class ClientFactory implements ClientFactoryInterface
{
    /** @var ClientInterface */
    protected $httpClient;
    /** @var RequestFactoryInterface */
    protected $requestFactory;
    /** @var StreamFactoryInterface */
    protected $streamFactory;
    public function __construct(ClientInterface $httpClient, RequestFactoryInterface $requestFactory, StreamFactoryInterface $streamFactory)
    {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }
    /**
     * @inheritDoc
     */
    public function createClientForApi(UriInterface $baseUrl, TokenAwareInterface $tokenProvider) : ApiClientInterface
    {
        $product = new ApiClient($this->httpClient, $this->requestFactory, $baseUrl, $this->streamFactory, $tokenProvider);
        return $product;
    }
}
