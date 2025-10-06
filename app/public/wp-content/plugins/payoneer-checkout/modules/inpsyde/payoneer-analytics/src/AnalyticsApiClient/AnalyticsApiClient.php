<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Analytics\AnalyticsApiClient;

use InvalidArgumentException;
use Syde\Vendor\Psr\Http\Client\ClientInterface;
use Syde\Vendor\Psr\Http\Message\RequestFactoryInterface;
use Syde\Vendor\Psr\Http\Message\RequestInterface;
use Syde\Vendor\Psr\Http\Message\StreamFactoryInterface;
use Syde\Vendor\Psr\Http\Message\StreamInterface;
use Throwable;
class AnalyticsApiClient implements AnalyticsApiClientInterface
{
    /**
     * @var string
     */
    protected $targetUrl;
    /**
     * @var RequestFactoryInterface
     */
    protected $requestFactory;
    /**
     * @var ClientInterface
     */
    protected $client;
    /**
     * @var StreamFactoryInterface
     */
    protected $streamFactory;
    /**
     * @param string $targetUrl
     * @param RequestFactoryInterface $requestFactory
     * @param ClientInterface $client
     * @param StreamFactoryInterface $streamFactory
     */
    public function __construct(string $targetUrl, RequestFactoryInterface $requestFactory, ClientInterface $client, StreamFactoryInterface $streamFactory)
    {
        $this->targetUrl = $targetUrl;
        $this->requestFactory = $requestFactory;
        $this->client = $client;
        $this->streamFactory = $streamFactory;
    }
    /**
     * @param string $payload
     */
    public function post(string $payload): void
    {
        try {
            $request = $this->createRequestFromPayload($payload);
            $response = $this->client->sendRequest($request);
        } catch (Throwable $exception) {
            do_action('payoneer_checkout.analytics_request_failed', ['exception' => $exception]);
            return;
        }
        do_action('payoneer_checkout.analytics_request_sent', ['request' => $request, 'response' => $response]);
    }
    /**
     * Create a request from provided payload.
     *
     * @param string $payload Payload to be sent.
     *
     * @return RequestInterface Ready request.
     */
    protected function createRequestFromPayload(string $payload): RequestInterface
    {
        $request = $this->requestFactory->createRequest('POST', $this->targetUrl);
        $body = $this->createBodyFromPayload($payload);
        return $request->withBody($body)->withHeader('Content-Type', 'application/json');
    }
    /**
     * Create a stream from payload object that can be used as a request body.
     *
     * @param string $payload
     *
     * @return StreamInterface
     *
     * @throws InvalidArgumentException If payload is not valid JSON.
     */
    protected function createBodyFromPayload(string $payload): StreamInterface
    {
        $encoded = json_encode(json_decode($payload));
        if (json_last_error() !== \JSON_ERROR_NONE) {
            throw new InvalidArgumentException(sprintf('Provided string "%1$s" is not a valid JSON.', $payload));
        }
        return $this->streamFactory->createStream((string) $encoded);
    }
}
