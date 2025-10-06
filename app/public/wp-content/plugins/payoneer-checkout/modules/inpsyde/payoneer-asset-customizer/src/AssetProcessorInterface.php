<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\AssetCustomizer;

use Syde\Vendor\Psr\Http\Message\UriInterface;
use Stringable;
/**
 * Something that can process an asset.
 *
 * @psalm-type MapOfScalars = array<string, Stringable|scalar|array<string, Stringable|scalar>>
 * @psalm-type Options = array<string, Stringable|scalar|MapOfScalars>
 */
interface AssetProcessorInterface
{
    /**
     * Given an asset path, creates a new asset changed according to the given options.
     *
     * @param string $path A path to an asset.
     * @param array<string, mixed> $options A map of processing option keys to their values.
     * @psalm-param Options $options
     *
     * @return UriInterface The URI of the new asset.
     */
    public function process(string $path, array $options) : UriInterface;
}
