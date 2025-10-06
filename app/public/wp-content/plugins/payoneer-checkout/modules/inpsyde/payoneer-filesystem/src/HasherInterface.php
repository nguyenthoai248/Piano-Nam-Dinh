<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use Syde\Vendor\Psr\Http\Message\StreamInterface;
use RuntimeException;
use UnexpectedValueException;
/**
 * Something that can hash a stream.
 */
interface HasherInterface
{
    /**
     * Retrieves the hash of a given stream.
     *
     * @param StreamInterface $stream The stream to hash.
     *
     * @return string The hash.
     * @throws UnexpectedValueException If stream is not readable.
     * @throws RuntimeException If problem hashing.
     */
    public function getHash(StreamInterface $stream) : string;
}
