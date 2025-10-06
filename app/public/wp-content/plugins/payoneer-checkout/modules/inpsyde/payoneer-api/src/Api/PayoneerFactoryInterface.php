<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Api;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\PayoneerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\ApiClientInterface;
use RuntimeException;
/**
 * Something that can create an object representing the Payoneer API.
 */
interface PayoneerFactoryInterface
{
    /**
     * Creates a Payoneer instance for an API.
     *
     * @param ApiClientInterface $apiClient The client used to perform requests to the API.
     *
     * @return PayoneerInterface The new Payoneer instance.
     *
     * @throws RuntimeException If problem creating.
     */
    public function createPayoneerForApi(ApiClientInterface $apiClient): PayoneerInterface;
}
