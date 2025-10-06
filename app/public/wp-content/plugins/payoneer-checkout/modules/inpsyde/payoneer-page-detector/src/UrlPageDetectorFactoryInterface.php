<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PageDetector;

use RuntimeException;
/**
 * Something that can create a new page detector from base URL params.
 */
interface UrlPageDetectorFactoryInterface
{
    /**
     * Creates a new page detector.
     *
     * @param string $baseUrl The base URL to compare parameters to.
     * @param string $basePath The base path that will not factor into path comparison.
     *
     * @return PageDetectorInterface The new page detector.
     *
     * @throws RuntimeException If problem creating.
     */
    public function createPageDetectorForBaseUrl(string $baseUrl, string $basePath): PageDetectorInterface;
}
