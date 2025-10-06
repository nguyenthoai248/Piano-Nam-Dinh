<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Cache;

use Syde\Vendor\Psr\Http\Message\StreamInterface;
use RuntimeException;
use Stringable;
/**
 * Something that can cache content to the filesystem on demand.
 *
 * @psalm-type MapOfScalars = array<string, Stringable|scalar|array<string, Stringable|scalar>>
 * @psalm-type Options = array<string, Stringable|scalar|MapOfScalars>
 * @psalm-type Context = array<string, Stringable|scalar|Options>
 */
interface FileCacheInterface
{
    /**
     * Retrieves the file with contents created by a generator for the specified key and context.
     *
     * @param string $key The cache key to use.
     * @param array<string, mixed> $context The context.
     * @psalm-param Context $context
     * @param callable(array<string, mixed>): StreamInterface $generator The content generator.
     * @psalm-param callable(Context): StreamInterface $generator
     *
     * @return string The path to the cached file.
     * @throws RuntimeException If problem retrieving.
     */
    public function getCachedFile(string $key, array $context, callable $generator) : string;
}
