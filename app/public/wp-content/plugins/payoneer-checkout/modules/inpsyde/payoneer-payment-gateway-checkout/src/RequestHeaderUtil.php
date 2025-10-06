<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout;

/**
 * Utility class for handling HTTP request headers.
 *
 * This class provides methods to check the presence of a header and retrieve its value.
 * It also includes a fallback mechanism to extract headers from the $_SERVER superglobal
 * when the `getallheaders` function is unavailable.
 * //TODO This is currently being used as a global utility. Probably it should be turned into a service
 *
 * phpcs:disable Inpsyde.CodeQuality.NoAccessors.NoGetter
 */
class RequestHeaderUtil
{
    /**
     * Checks if a specific HTTP header exists in the request.
     *
     * @param string $headerName The name of the header to check.
     *
     * @return bool Returns true if the header exists, false otherwise.
     */
    public function hasHeader(string $headerName): bool
    {
        $headers = $this->getHeaders();
        return array_key_exists(strtolower($headerName), $headers);
    }
    /**
     * Retrieves the value of a specific HTTP header from the request.
     *
     * @param string $headerName The name of the header to retrieve.
     *
     * @return string Returns the value of the header, or an empty string if the header does not
     *     exist.
     */
    public function getHeader(string $headerName): string
    {
        $headers = $this->getHeaders();
        return (string) ($headers[strtolower($headerName)] ?? '');
    }
    /**
     * Retrieves all HTTP headers from the request.
     *
     * This method uses the `getallheaders` function if available, otherwise it falls back to
     * extracting headers from the $_SERVER superglobal.
     *
     * @return array An associative array of header names and their corresponding values.
     */
    public function getHeaders(): array
    {
        //phpcs:disable WordPress.CodeAnalysis.AssignmentInCondition.FoundInTernaryCondition
        $headers = function_exists('getallheaders') && is_array($headers = getallheaders()) ? array_change_key_case($headers, \CASE_LOWER) : $this->getHeadersFromServer();
        return $headers;
    }
    /**
     * Fallback if getallheaders is unavailable
     *
     * This private method retrieves headers from the $_SERVER superglobal when the `getallheaders`
     * function is not available. It processes server variables to extract HTTP headers.
     *
     * @return array An associative array of header names and their corresponding values.
     */
    private function getHeadersFromServer(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headerName = strtolower(str_replace('_', '-', (string) substr($key, 5)));
                $headers[$headerName] = $value;
            }
        }
        return $headers;
    }
}
