<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout;

use Syde\Vendor\Psr\Http\Client\ClientInterface;
use Syde\Vendor\Psr\Http\Message\RequestInterface;
use Syde\Vendor\Psr\Http\Message\ResponseInterface;
/**
 * API calls - especially the CHARGE call - can take longer
 * than the 5 seconds WordPress uses as default.
 * This decorator temporarily extends the timeout for each individual API call
 * without changing the global configuration
 */
class TimeoutIncreasingApiClient implements ClientInterface
{
    /**
     * @var ClientInterface
     */
    protected $base;
    private const TIMEOUT_HOOK = 'http_request_timeout';
    /**
     * @var \Closure
     */
    protected $filter;
    public function __construct(ClientInterface $base, int $timeout)
    {
        $this->base = $base;
        $this->filter = function () use ($timeout): int {
            remove_action(self::TIMEOUT_HOOK, $this->filter);
            return $timeout;
        };
    }
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        add_filter(self::TIMEOUT_HOOK, $this->filter);
        return $this->base->sendRequest($request);
    }
}
