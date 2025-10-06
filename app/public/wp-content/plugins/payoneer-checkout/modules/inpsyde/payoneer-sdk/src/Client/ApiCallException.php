<?php

/**
 * Inpsyde\PayoneerSdk\Client\ApiCallException
 *
 * Exception for API call errors.
 */
declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Client;

use Syde\Vendor\Psr\Http\Message\RequestInterface;
use Syde\Vendor\Psr\Http\Message\ResponseInterface;
/**
 * Exception for API call errors.
 */
class ApiCallException extends ApiClientException implements ApiCallExceptionInterface
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var ?ResponseInterface
     */
    private ?ResponseInterface $response;
    /**
     * @param ApiClientInterface $apiClient
     * @param RequestInterface $request
     * @param ResponseInterface|null $response
     * @param \Throwable|null $previous
     */
    public function __construct(ApiClientInterface $apiClient, RequestInterface $request, ResponseInterface $response = null, \Throwable $previous = null)
    {
        $this->request = $request;
        $this->response = $response;
        $statusCode = 0;
        if ($response) {
            $response->getStatusCode();
        }
        parent::__construct($apiClient, sprintf('Api request failed with status code %1$d.', $statusCode), $statusCode, $previous);
    }
    /**
     * Gets the request that was sent to the API.
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
    /**
     * Gets the response from the API.
     *
     * @return ?ResponseInterface
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}
