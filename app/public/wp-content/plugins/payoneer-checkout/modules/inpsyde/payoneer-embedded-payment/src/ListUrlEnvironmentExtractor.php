<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment;

class ListUrlEnvironmentExtractor
{
    public function extract(string $url): string
    {
        $hostName = parse_url($url, \PHP_URL_HOST);
        if (!is_string($hostName)) {
            throw new \InvalidArgumentException('Provided URL does not contain a valid hostname.');
        }
        preg_match('/^api.(.*?).oscato.com$/i', $hostName, $matches);
        if (!isset($matches[1])) {
            throw new \InvalidArgumentException('Provided URL does not contain a valid environment');
        }
        return $matches[1];
    }
}
