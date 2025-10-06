<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use Syde\Vendor\Psr\Http\Message\StreamInterface;
use RangeException;
use RuntimeException;
/**
 * A factory that can create a stream for a file.
 */
interface FileStreamFactoryInterface
{
    /**
     * Creates a stream that exposes the contents of a file at the specified path.
     *
     * @param string $path The path to the file.
     * @param string $mode The mode to {@link fopen() open the file} in.
     *
     * @return StreamInterface The stream that exposes the file contents.
     *
     * @throws RangeException If the mode is invalid.
     * @throws RuntimeException If problem creating.
     */
    public function createStreamFromFile(string $path, string $mode) : StreamInterface;
}
