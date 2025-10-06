<?php

/**
 * Inpsyde\PayoneerSdk\Client\ApiCallExceptionInterface
 *
 * Should be thrown when API response reports error or API response cannot be parsed.
 */
declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Client;

use Syde\Vendor\Psr\Http\Message\RequestInterface;
use Syde\Vendor\Psr\Http\Message\ResponseInterface;
/**
 * Interface for API call exceptions.
 */
interface ApiCallExceptionInterface extends ApiClientExceptionInterface
{
    /**
     * Gets the request that was sent to the API.
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface;
    /**
     * Gets the response from the API.
     *
     * @return ?ResponseInterface
     */
    public function getResponse(): ?ResponseInterface;
}
