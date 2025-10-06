<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PageDetector;

/**
 * Something that can detect whether the current page corresponds to the specified parameters.
 *
 * @psalm-type Path = string | string[]
 * @psalm-type Query = string | array<string, string>
 * @psalm-type UrlParts = array{
 *      scheme?: string,
 *      host?: string,
 *      user?: string,
 *      pass?: string,
 *      port?: int,
 *      path?: Path,
 *      query?: Query,
 *      fragment?: string,
 * }
 */
interface PageDetectorInterface
{
    /**
     * Detects whether the current page corresponds to the specified parameters.
     *
     * @param mixed[] $criteria Parameters to compare. See {@see parse_url()}.
     *
     * @psalm-param UrlParts $criteria
     *
     * @return bool True if the current page corresponds
     */
    public function isPage(array $criteria): bool;
}
