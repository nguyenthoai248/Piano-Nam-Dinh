<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use RuntimeException;
/**
 * Resolves paths by substituting a matching path prefix.
 */
class PrefixMatchingPathResolver implements PathResolverInterface
{
    use PathHelperTrait;
    use RegexTrait;
    /**
     * @var array<string, string>
     */
    protected $prefixMap;
    /**
     * @var string
     */
    protected $basePath;
    /**
     * @param array<string, string> $prefixMap The map of source to destination path prefixes.
     *                                         Directory prefixes in both cases must terminate with
     *                                         to avoid false positive matches and nad replacement.
     * @param string $basePath                 The base path in the filesystem. Resolved paths
     *                                         will have this prepended.
     */
    public function __construct(array $prefixMap, string $basePath)
    {
        $this->prefixMap = $prefixMap;
        $this->basePath = $this->normalizePath($basePath);
    }
    /**
     * @inheritDoc
     */
    public function resolvePath(string $sourcePath) : string
    {
        $prefixed = $this->substitutePrefix($sourcePath);
        $prefixed = $this->normalizePath($prefixed);
        $basePath = $this->basePath;
        $resolved = empty($basePath) ? $prefixed : "{$basePath}/{$prefixed}";
        return $resolved;
    }
    /**
     * Substitutes the longest configured source prefix in the string
     * with a corresponding destination prefix.
     *
     * @param string $string The string to substitute the prefix in.
     *
     * @return string The string with the matched prefix substituted.
     * @throws RuntimeException If problem substituting.
     */
    protected function substitutePrefix(string $string) : string
    {
        $delim = '!';
        foreach ($this->getSortedPrefixMap(\false) as $sourcePrefix => $destinationPrefix) {
            if ($this->stringStartsWith($string, $sourcePrefix)) {
                return $this->pregReplace("{$delim}^" . preg_quote($sourcePrefix, $delim) . $delim, $destinationPrefix, $string, 1);
            }
        }
        throw new RuntimeException(sprintf('No prefix found for source path "%1$s"', $string));
    }
    /**
     * Retrieves the prefix map, sorted by source prefix.
     *
     * @param bool $isAscending If true, sorts in ascending order.
     *                          Otherwise, the order is descending.
     *
     * @return array<string, string> The map of source prefixes to destination prefixes.
     */
    protected function getSortedPrefixMap(bool $isAscending) : array
    {
        $map = $this->sortArrayByKeyLength($this->prefixMap, \false);
        return $map;
    }
    /**
     * Determines whether the specified string starts with the specified prefix.
     *
     * @param string $string The string to check.
     * @param string $prefix The prefix to check for.
     *
     * @return bool True if the string starts with the prefix; false otherwise.
     * @throws RuntimeException If problem determining.
     */
    protected function stringStartsWith(string $string, string $prefix) : bool
    {
        $delim = '!';
        return $this->pregMatch("{$delim}^" . preg_quote($prefix, $delim) . $delim, $string);
    }
    /**
     * Sorts an array by key length.
     *
     * @template E
     * @param array<string, mixed> $array The array to sort.
     * @psalm-param array<string, E> $array
     * @param bool $isAscending If true, the order will be ascending; otherwise, descending.
     *
     * @return array<string, mixed> The sorted array
     * @psalm-return array<string, E>
     * @throws RuntimeException If problem sorting.
     */
    protected function sortArrayByKeyLength(array $array, bool $isAscending) : array
    {
        uksort($array, static function (string $first, string $second) use($isAscending) : int {
            $aLength = strlen($first);
            $bLength = strlen($second);
            return $isAscending ? $aLength <=> $bLength : $bLength <=> $aLength;
        });
        return $array;
    }
}
