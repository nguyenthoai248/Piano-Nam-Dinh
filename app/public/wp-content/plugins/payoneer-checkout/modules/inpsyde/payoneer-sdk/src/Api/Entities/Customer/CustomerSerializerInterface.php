<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer;

/**
 * Service able to convert CustomerSerializerInterface instance to array.
 */
interface CustomerSerializerInterface
{
    /**
     * Create an array from Customer object.
     *
     * @param CustomerInterface $customer Object containing data.
     *
     * @return array {
     *     number?: string,
     *     phones?: array{mobile: array{unstructuredNumber: string}},
     *     addresses?: array{billing: AddressInterface, shipping?: AddressInterface},
     *     email?: string,
     *     deliveryEmail?: string,
     *     registration?: array {
     *         id: string
     *     },
     *     name?: array {
     *         firstName: string,
     *         lastName: string
     * }
     * } Resulting array.
     */
    public function serializeCustomer(CustomerInterface $customer): array;
}
