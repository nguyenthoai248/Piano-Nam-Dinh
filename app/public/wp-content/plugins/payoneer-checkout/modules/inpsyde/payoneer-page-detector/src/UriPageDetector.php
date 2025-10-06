<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PageDetector;

use RangeException;
use RuntimeException;
/**
 * Detects whether the current page corresponds to the specified parameters,
 * based on a current URL.
 *
 * @psalm-type Path = string | list<string>
 * @psalm-type Query = string | array<array-key, mixed>
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
class UriPageDetector implements PageDetectorInterface
{
    protected const CHARS_WHITESPACE = " \t\n\r\x00\v";
    /**
     * @var array
     * @psalm-var UrlParts
     */
    protected $currentParts;
    /** @var string[] */
    protected $basePath;
    /**
     * @var string
     */
    protected $pathSegmentSeparator;
    /**
     * @param string $currentUrl
     * @param string[] $basePath
     * @param string $pathSegmentSeparator
     *
     * @throws RuntimeException If problem constructing.
     * @throws RangeException If URL is malformed.
     */
    public function __construct(string $currentUrl, array $basePath, string $pathSegmentSeparator = '/')
    {
        $this->pathSegmentSeparator = $pathSegmentSeparator;
        $this->currentParts = $this->parseUrl($currentUrl);
        $this->basePath = $basePath;
    }
    /**
     * @inheritDoc
     */
    public function isPage(array $criteria): bool
    {
        $parts = $this->currentParts;
        $query = $parts['query'] ?? [];
        unset($parts['query']);
        $path = $parts['path'] ?? [];
        unset($parts['path']);
        foreach ($parts as $key => $value) {
            if (isset($criteria[$key]) && !is_array($criteria[$key])) {
                if (!$this->isMatchParam($criteria[$key], $value)) {
                    return \false;
                }
            }
        }
        if (array_key_exists('query', $criteria) && is_array($query)) {
            if (!$this->isMatchQuery($criteria['query'], $query)) {
                return \false;
            }
        }
        if (array_key_exists('path', $criteria) && is_array($path)) {
            if (!$this->isMatchPath($criteria['path'], $path)) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * Retrieves parts of a URL, according to spec.
     *
     * @param string $url
     *
     * @return array<string, mixed> The parts of the URL.
     * @psalm-return UrlParts
     *
     * @throws RuntimeException If retrieving.
     * @throws RangeException If URL is malformed.
     */
    protected function parseUrl(string $url): array
    {
        $sep = $this->pathSegmentSeparator;
        $parts = parse_url($url);
        if ($parts === \false) {
            throw new RangeException('URL is malformed');
        }
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
            $parts['query'] = $query;
        }
        if (isset($parts['path'])) {
            $path = explode($sep, $parts['path']);
            $parts['path'] = $path;
        }
        $parts = array_filter($parts);
        return $parts;
    }
    /**
     * Determines whether an actual param value matches the expected.
     *
     * @param mixed $expected The expected value.
     * @param mixed $actual The actual value.
     *
     * @return bool True if the values match; false otherwise;
     *
     * @throws RuntimeException If problem determining.
     */
    protected function isMatchParam($expected, $actual): bool
    {
        return $actual === $expected;
    }
    /**
     * Determines whether an actual query matches the expected.
     *
     * @param array|string $expected The expected query or query param map.
     * @psalm-param Query $expected
     * @param array<array-key, mixed> $actual The actual query param map.
     *
     * @return bool True if the queries match; false otherwise;
     *
     * @throws RuntimeException If problem determining.
     */
    protected function isMatchQuery($expected, array $actual): bool
    {
        if (is_string($expected)) {
            $actual = http_build_query($actual);
            return $this->isMatchParam($expected, $actual);
        }
        foreach ($expected as $key => $value) {
            if (!$this->isMatchParam($value, $actual[$key] ?? null)) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * Determines whether an actual path matches the expected.
     *
     * @param string|string[] $expected The expected path or list of path segments.
     * @param string[] $actual The actual path.
     *
     * @return bool True if the paths match; false otherwise.
     *
     * @throws RuntimeException If problem determining.
     */
    protected function isMatchPath($expected, array $actual): bool
    {
        $sep = $this->pathSegmentSeparator;
        $actual = implode($sep, $actual);
        if (!is_string($expected)) {
            $expected = implode($sep, $expected);
        }
        $actual = $this->normalizePath($actual);
        $expected = $this->normalizePath($expected);
        $isMatch = $expected === $actual;
        return $isMatch;
    }
    /**
     * Retrieves a sanitized version of the specified path.
     *
     * @param string $path The path to sanitize.
     *
     * @return string The sanitized path.
     */
    protected function sanitizePath(string $path): string
    {
        /**
         * @var string $whitespace
         */
        $whitespace = static::CHARS_WHITESPACE;
        $path = trim($path, "{$whitespace}{$this->pathSegmentSeparator}");
        return $path;
    }
    /**
     * Determines whether the specified string starts with the specified prefix.
     *
     * @param string $prefix The prefix to check for.
     * @param string $string The string to check for the prefix in.
     *
     * @return bool True if the string starts with the specified prefix; false otherwise.
     *
     * @throws RuntimeException If problem determining.
     */
    protected function isStringStartsWith(string $prefix, string $string): bool
    {
        $start = substr($string, 0, strlen($prefix));
        $isMatch = $start === $prefix;
        return $isMatch;
    }
    /**
     * Retrieves the relative path to the specified path.
     *
     * @param string $path The possibly absolute path.
     *
     * @return string The relative path.
     *
     * @throws RuntimeException If problem retrieving.
     */
    protected function normalizePath(string $path): string
    {
        $sep = $this->pathSegmentSeparator;
        $basePath = implode($sep, $this->basePath);
        if ($this->isStringStartsWith($sep, $path)) {
            $path = ltrim($path, $sep);
            if (!$this->isStringStartsWith($basePath, $path)) {
                throw new RangeException(sprintf('Base path "%1$s" not found in path "%2$s', $basePath, $path));
            }
            $path = substr($path, strlen($basePath));
        }
        $path = $this->sanitizePath((string) $path);
        return $path;
    }
}
