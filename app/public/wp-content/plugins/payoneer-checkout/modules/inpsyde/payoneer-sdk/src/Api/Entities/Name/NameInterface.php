<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Name;

/**
 * Represents customer's name.
 */
interface NameInterface
{
    /**
     * Return customer's first name.
     *
     * @return string First name.
     */
    public function getFirstName(): string;
    /**
     * Return customer's last name.
     *
     * @return string Last name.
     */
    public function getLastName(): string;
}
