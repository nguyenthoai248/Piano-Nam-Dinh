<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use InvalidArgumentException;
use Syde\Vendor\Psr\Http\Message\UriFactoryInterface;
use Syde\Vendor\Psr\Http\Message\UriInterface;
/**
 * Parses and creates a URI.
 */
class UriFactory implements UriFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createUri(string $uri = '') : UriInterface
    {
        $result = new Uri(null, null, null, null, null, null, null, null);
        $parts = parse_url($uri);
        if ($parts === \false) {
            throw new InvalidArgumentException(sprintf('Unable to parse URI: "%1$s"', $uri));
        }
        if (isset($parts['scheme'])) {
            $result = $result->withScheme($parts['scheme']);
        }
        if (isset($parts['user'])) {
            $result = $result->withUserInfo($parts['user'], $parts['pass'] ?? null);
        }
        if (isset($parts['host'])) {
            $result = $result->withHost($parts['host']);
        }
        if (isset($parts['port'])) {
            $result = $result->withPort($parts['port']);
        }
        if (isset($parts['path'])) {
            $result = $result->withPath($parts['path']);
        }
        if (isset($parts['query'])) {
            $result = $result->withQuery($parts['query']);
        }
        if (isset($parts['fragment'])) {
            $result = $result->withFragment($parts['fragment']);
        }
        return $result;
    }
}
