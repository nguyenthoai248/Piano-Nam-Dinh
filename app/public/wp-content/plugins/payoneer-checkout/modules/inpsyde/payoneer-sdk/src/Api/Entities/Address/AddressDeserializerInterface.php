<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Address;

/**
 * A service able to convert array to an Address instance.
 */
interface AddressDeserializerInterface
{
    /**
     * @param array {
     *     country: string,
     *     city: string,
     *     street: string,
     *     postalCode: string,
     *     name?: array {
     *          firstName: string,
     *          lastName: string
     *     },
     *     state?: string
     * } $addressData
     *
     * @return AddressInterface Created Address instance.
     */
    public function deserializeAddress(array $addressData): AddressInterface;
}
