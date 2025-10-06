<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Analytics\AnalyticsApiClient;

interface AnalyticsApiClientInterface
{
    /**
     * POST analytics data to the API.
     *
     * @param string $payload Request payload (body contents).
     */
    public function post(string $payload): void;
}
