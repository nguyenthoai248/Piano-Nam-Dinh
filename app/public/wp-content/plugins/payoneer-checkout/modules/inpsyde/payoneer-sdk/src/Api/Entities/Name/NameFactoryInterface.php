<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Name;

/**
 * A service able to create a Name instance.
 */
interface NameFactoryInterface
{
    /**
     * Create a new name instance.
     *
     * @param string $firstName Customer's first name.
     * @param string $lastName Customer's last name.
     *
     * @return NameInterface Created name instance.
     */
    public function createName(string $firstName, string $lastName): NameInterface;
}
