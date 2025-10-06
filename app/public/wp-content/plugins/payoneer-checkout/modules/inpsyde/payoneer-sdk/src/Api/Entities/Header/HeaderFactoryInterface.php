<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Header;

/**
 * A service able to create a new Header instance.
 */
interface HeaderFactoryInterface
{
    /**
     * Create a new Header instance.
     *
     * @return HeaderInterface Created header.
     */
    public function createHeader(string $name, string $value): HeaderInterface;
}
