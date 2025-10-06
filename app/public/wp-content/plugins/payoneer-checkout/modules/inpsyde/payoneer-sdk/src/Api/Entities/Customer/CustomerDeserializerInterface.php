<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
/**
 * Service able to convert data array to a Customer object.
 */
interface CustomerDeserializerInterface
{
    /**
     * @param array {
     *     number?: string,
     *     phones?: array {
     *          mobile: array {
     *              'unstructuredNumber': string
     *          }
     *     },
     *     addresses?: array {
     *          billing: array{
     *              country: string,
     *              city: string,
     *              street: string
     *          },
     *          shipping?: array{
     *              country: string,
     *              city: string,
     *              street: string,
     *              name: array {
     *                  firstName: string,
     *                  lastName: string
     *              }
     *          }
     *     },
     *     email?: string,
     *     deliveryEmail?: string,
     *     registration?: array {
     *         id: string
     *     },
     *     name?: array {
     *         firstName: string,
     *         lastName: string
     * }
     * } $customerData Customer details.
     *
     * @return CustomerInterface Created instance.
     *
     * @throws ApiExceptionInterface If something went wrong.
     */
    public function deserializeCustomer(array $customerData): CustomerInterface;
}
