<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\PaymentProcessor;

use Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;

trait RedirectUrlCreatorTrait
{
    /**
     * If the LIST response contains a redirect object, craft a compatible URL
     * out of the given URL and its parameters. If none is found, use our own return URL
     * as a fallback
     *
     * @param ListInterface $response
     *
     * @return string
     * @throws ApiExceptionInterface
     */
    protected function createRedirectUrl(ListInterface $response): string
    {
        $redirect = $response->getRedirect();
        $baseUrl = $redirect->getUrl();
        $parameters = $redirect->getParameters();
        $parameterDict = [];
        array_walk($parameters, static function (array $param) use (&$parameterDict) {
            /** @psalm-suppress MixedArrayAssignment * */
            $parameterDict[(string)$param['name']] = urlencode((string)$param['value']);
        });

        return add_query_arg($parameterDict, $baseUrl);
    }
}
