<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Redirect;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
/**
 * A service able to convert an array to a Redirect instance.
 */
interface RedirectDeserializerInterface
{
    /**
     * @param array {
     *      url: string,
     *      method: string,
     *      type: string
     *      parameters: array
     * } $redirectData An array containing data about redirect.
     *
     * @return RedirectInterface Deserialized redirect.
     *
     * @throws ApiExceptionInterface If something went wrong.
     */
    public function deserializeRedirect(array $redirectData): RedirectInterface;
}
