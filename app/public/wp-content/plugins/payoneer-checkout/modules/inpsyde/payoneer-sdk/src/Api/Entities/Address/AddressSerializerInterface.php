<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Address;

/**
 * Service able to convert Address instance to array.
 */
interface AddressSerializerInterface
{
    /**
     * Convert Address instance to array.
     *
     * @return array{
     *     country: string,
     *     city: string,
     *     street: string,
     *     name?: array {
     *          firstName: string,
     *          lastName: string
     *     },
     *     state?: string
     * } Serialized address.
     */
    public function serializeAddress(AddressInterface $address): array;
}
