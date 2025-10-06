<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Client;

use InvalidArgumentException;
use Syde\Vendor\Psr\Http\Client\ClientExceptionInterface;
use Syde\Vendor\Psr\Http\Client\ClientInterface;
use Syde\Vendor\Psr\Http\Message\RequestFactoryInterface;
use Syde\Vendor\Psr\Http\Message\RequestInterface;
use Syde\Vendor\Psr\Http\Message\ResponseInterface;
use Syde\Vendor\Psr\Http\Message\StreamFactoryInterface;
use Syde\Vendor\Psr\Http\Message\StreamInterface;
use Syde\Vendor\Psr\Http\Message\UriInterface;
use RuntimeException;
class ApiClient implements ApiClientInterface
{
    protected const METHOD_GET = 'GET';
    protected const METHOD_POST = 'POST';
    protected const METHOD_PUT = 'PUT';
    /**
     * @var ClientInterface A service able to send HTTP request.
     */
    protected $httpClient;
    /**
     * @var RequestFactoryInterface A service able to create HTTP request.
     */
    protected $requestFactory;
    /**
     * @var UriInterface Base URL of request.
     */
    protected $baseUrl;
    /**
     * @var StreamFactoryInterface A service able to create a new stream from string.
     */
    protected $streamFactory;
    /**
     * @var TokenAwareInterface
     */
    protected $tokenProvider;
    /**
     * @param ClientInterface $httpClient A PSR-3 compatible http client to make requests to API.
     * @param RequestFactoryInterface $requestFactory
     * @param UriInterface $baseUrl
     * @param StreamFactoryInterface $streamFactory
     * @param TokenAwareInterface $tokenProvider
     */
    public function __construct(ClientInterface $httpClient, RequestFactoryInterface $requestFactory, UriInterface $baseUrl, StreamFactoryInterface $streamFactory, TokenAwareInterface $tokenProvider)
    {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->baseUrl = $baseUrl;
        $this->streamFactory = $streamFactory;
        $this->tokenProvider = $tokenProvider;
    }
    /**
     * @inheritDoc
     */
    public function get(string $path, array $headers, array $queryParams): ResponseInterface
    {
        $request = $this->prepareRequest(self::METHOD_GET, $path, $headers, $queryParams, []);
        return $this->sendRequest($request);
    }
    /**
     * @inheritDoc
     */
    public function post(string $path, array $headers, array $queryParams, array $bodyParams): ResponseInterface
    {
        $request = $this->prepareRequest(self::METHOD_POST, $path, $headers, $queryParams, $bodyParams);
        return $this->sendRequest($request);
    }
    /**
     * @inheritDoc
     */
    public function put(string $path, array $headers, array $queryParams, array $bodyParams): ResponseInterface
    {
        $request = $this->prepareRequest(self::METHOD_PUT, $path, $headers, $queryParams, $bodyParams);
        return $this->sendRequest($request);
    }
    /**
     * Create a new request with provided data.
     *
     * @param string $method
     * @param string $path
     * @param array $headers
     * @param array $queryParams
     * @param array $bodyParams
     *
     * @return RequestInterface
     * @throws ApiClientException
     */
    protected function prepareRequest(string $method, string $path, array $headers, array $queryParams, array $bodyParams): RequestInterface
    {
        $requestUrl = $this->prepareRequestUrl($path, $queryParams);
        $request = $this->requestFactory->createRequest($method, $requestUrl);
        try {
            foreach ($headers as $header => $value) {
                $request = $request->withAddedHeader($header, $value);
            }
            $token = $this->tokenProvider->getToken();
            if (!$request->hasHeader('Authorization')) {
                $request = $request->withHeader('Authorization', $token);
            }
            if ($method === self::METHOD_GET) {
                return $request;
            }
            return $request->withBody($this->prepareBody($bodyParams));
        } catch (InvalidArgumentException|RuntimeException $exception) {
            throw new ApiClientException($this, 'Failed to prepare request', 0, $exception);
        }
    }
    /**
     * @param string $path
     * @param array $queryParams
     *
     * @return UriInterface
     *
     * @throws ApiClientException
     */
    protected function prepareRequestUrl(string $path, array $queryParams): UriInterface
    {
        $existingUrlPath = rtrim($this->baseUrl->getPath(), '/\\');
        $fullPath = sprintf('%1$s/%2$s', $existingUrlPath, $path);
        try {
            $requestUrl = $this->baseUrl->withPath($fullPath);
            if ($queryParams) {
                $existingQuery = $requestUrl->getQuery();
                $separator = $existingQuery === '' ? '' : '&';
                $query = http_build_query($queryParams);
                $query = $existingQuery . $separator . $query;
                $requestUrl = $requestUrl->withQuery($query);
            }
        } catch (InvalidArgumentException $exception) {
            throw new ApiClientException($this, 'Failed to prepare request. Exception caught when trying to build request URL.', 0, $exception);
        }
        return $requestUrl;
    }
    /**
     * Convert array into StreamInterface with JSON-encoded string as a content.
     *
     * @param array<array-key, mixed> $params Data to be converted to JSON.
     *
     * @return StreamInterface The body data stream.
     *
     * @throws RuntimeException If failed to encode params.
     */
    protected function prepareBody(array $params): StreamInterface
    {
        $json = $this->jsonEncode($params);
        return $this->streamFactory->createStream($json);
    }
    /**
     * Send given HTTP request and return a response.
     *
     * @param RequestInterface $request The request to send.
     *
     * @return ResponseInterface The HTTP response on success.
     *
     * @throws ApiClientExceptionInterface If HTTP client throwing exception or response code >=300.
     */
    protected function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            throw new ApiClientException($this, sprintf('Api request failed. Exception was caught when trying to send request: %1$s', $exception->getMessage()), (int) $exception->getCode(), $exception);
        }
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 300) {
            throw new ApiCallException($this, $request, $response);
        }
        return $response;
    }
    /**
     * Encodes a value by representing it as a JSON string.
     *
     * @param scalar|array|object $value The value to encode.
     * @return string The JSON representing the value.
     *
     * @throws RuntimeException If problem encoding.
     */
    protected function jsonEncode($value): string
    {
        $json = json_encode($value);
        if ($json === \false || json_last_error() !== \JSON_ERROR_NONE) {
            throw new RuntimeException(json_last_error_msg());
        }
        return $json;
    }
}
