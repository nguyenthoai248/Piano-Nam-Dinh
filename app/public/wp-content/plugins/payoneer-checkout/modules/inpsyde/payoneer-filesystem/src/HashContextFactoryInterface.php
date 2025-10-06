<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use RuntimeException;
/**
 * Something that can create a hash context.
 */
interface HashContextFactoryInterface
{
    /**
     * Creates a hash context.
     *
     * @return HashContextInterface The new context.
     *
     * @throws RuntimeException If problem creating.
     */
    public function createHashContext() : HashContextInterface;
}
