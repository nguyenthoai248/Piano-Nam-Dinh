<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use RuntimeException;
/**
 * Something that can represent a hash context.
 */
interface HashContextInterface
{
    /**
     * Initializes the context.
     *
     * @throws RuntimeException If problem initializing.
     */
    public function init() : void;
    /**
     * Updates the context with new data.
     *
     * @param string $data The data to update the context with.
     *
     * @throws RuntimeException If problem updating.
     */
    public function update(string $data) : void;
    /**
     * Copies the context.
     *
     * @return static The new context that is identical to the previous, yet separate.
     *
     * @throws RuntimeException If problem copying.
     */
    public function copy() : self;
    /**
     * Retrieves the hash digest, finalizing the hash.
     *
     * @param bool $isHex If true, the hash is retrieved in the form of lowercase hexits;
     *                    otherwise, binary data.
     *
     * @return string The hash digest.
     *
     * @throws RuntimeException If problem retrieving.
     */
    public function finalize(bool $isHex) : string;
}
