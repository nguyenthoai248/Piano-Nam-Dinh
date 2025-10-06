<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Redirect;

/**
 * A service able to create a new redirect instance.
 */
interface RedirectFactoryInterface
{
    /**
     * Create a new Redirect instance.
     *
     * @param string $url A redirect URL.
     * @param string $method Allowed redirect HTTP method.
     * @param string $type Redirect type.
     * @param array $parameters Parameter array
     *
     * @return RedirectInterface Created redirect instance.
     */
    public function createRedirect(string $url, string $method, string $type, array $parameters): RedirectInterface;
}
