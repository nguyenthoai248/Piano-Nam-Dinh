<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Redirect;

/**
 * Represents redirect data object sent back from the Payoneer API.
 */
interface RedirectInterface
{
    /**
     * The URL where customer should be redirected.
     *
     * @return string A string representing redirection URL.
     */
    public function getUrl(): string;
    /**
     *  Advised HTTP method to use for the redirection.
     *
     * @return string Either 'GET' or 'POST'.
     */
    public function getMethod(): string;
    /**
     * Return redirection type.
     *
     * One of 'DEFAULT', 'SUMMARY', 'RETURN', 'CANCEL', 'PROVIDER' or '3DS2-HANDLER'.
     *
     * @return string A string representing redirection type.
     */
    public function getType(): string;
    /**
     * An array of parameters to be sent with the request. In case of a POST redirect,
     * the parameters should be sent as in a form submission with header
     * Content-type = application/x-www-form-urlencoded,
     * in a GET redirect the parameters should be attached to the main URL as query string elements.
     *
     * If account placeholders are used (see next item on this list)
     * the following key-value pairs can be present and should be replaced
     * by the respective input form data collected from the end user:
     *
     * holderName: ${account.holderName}
     * cardNumber: ${account.number}
     * expiryMonth: ${account.expiryMonth}
     * expiryYear: ${account.expiryYear}
     * verificationCode: ${account.verificationCode}
     *
     * @return array A string representing redirection type.
     */
    public function getParameters(): array;
}
