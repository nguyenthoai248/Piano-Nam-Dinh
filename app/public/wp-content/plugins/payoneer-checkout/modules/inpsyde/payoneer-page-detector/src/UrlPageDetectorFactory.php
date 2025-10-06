<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PageDetector;

/**
 * Can create a new page detector from base URL params.
 */
class UrlPageDetectorFactory implements UrlPageDetectorFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createPageDetectorForBaseUrl(string $baseUrl, string $basePath): PageDetectorInterface
    {
        return new UriPageDetector($baseUrl, explode('/', $basePath));
    }
}
