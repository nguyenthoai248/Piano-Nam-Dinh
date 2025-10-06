<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Client;

use Syde\Vendor\Psr\Http\Message\ResponseInterface;
/**
 * High-level service handling communications with the Payoneer API.
 */
interface ApiClientInterface
{
    /**
     * @param string $path The endpoint path, relative to the base URL.
     * @param array<string, string> $headers The request headers.
     * @param array<array-key, mixed> $queryParams Params that will be transformed to a request
     *                                              URL query.
     *
     * @return ResponseInterface The response from the API.
     *
     * @throws ApiClientExceptionInterface If something went wrong.
     */
    public function get(string $path, array $headers, array $queryParams): ResponseInterface;
    /**
     * @param string $path The endpoint path, relative to the base URL.
     * @param array<string, string> $headers The request headers.
     * @param array<array-key, mixed> $queryParams Params that will be transformed to a request
     *                                              URL query.
     * @param array<array-key, mixed> $bodyParams Params in the free-form associative array,
     *                                        that will be transformed to a request body.
     *
     * @return ResponseInterface The response from the API.
     *
     * @throws ApiClientExceptionInterface If something went wrong.
     */
    public function post(string $path, array $headers, array $queryParams, array $bodyParams): ResponseInterface;
    /**
     * @param string $path The endpoint path, relative to the base URL.
     * @param array<string, string> $headers The request headers.
     * @param array<array-key, mixed> $queryParams Params that will be transformed to a request
     *                                              URL query.
     * @param array<array-key, mixed> $bodyParams Params in the free-form associative array,
     *                                        that will be transformed to a request body.
     *
     * @return ResponseInterface The response from the API.
     *
     * @throws ApiClientExceptionInterface If something went wrong.
     */
    public function put(string $path, array $headers, array $queryParams, array $bodyParams): ResponseInterface;
}
