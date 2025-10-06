<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Address;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Name\NameInterface;
/**
 * Represents a service able to create an Address instance.
 */
interface AddressFactoryInterface
{
    /**
     * @param string $country
     * @param string $city
     * @param string $street
     * @param string $postalCode
     * @param NameInterface|null $name,
     * @param string|null $state
     *
     * @return AddressInterface
     */
    public function createAddress(string $country, string $city, string $street, string $postalCode, NameInterface $name = null, string $state = null): AddressInterface;
}
