<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use Syde\Vendor\Psr\Http\Message\StreamInterface;
/**
 * A factory that can create a stream from a string.
 */
interface StringStreamFactoryInterface
{
    /**
     * Creates a stream that exposes the specified contents.
     *
     * @param string $contents The contents for the stream to expose.
     *
     * @return StreamInterface The stream that exposes the specified contents.
     */
    public function createStreamFromString(string $contents) : StreamInterface;
}
