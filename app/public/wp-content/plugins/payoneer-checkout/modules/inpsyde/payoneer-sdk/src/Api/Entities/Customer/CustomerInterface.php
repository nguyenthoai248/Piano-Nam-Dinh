<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Name\NameInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Phone\PhoneInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Registration\RegistrationInterface;
/**
 * Represents customer in the LIST session.
 */
interface CustomerInterface
{
    /**
     * @return string Customer identifier given by merchant.
     *
     * @throws ApiExceptionInterface If this field not set.
     */
    public function getNumber(): string;
    /**
     * Return customer email.
     *
     * @return string Customer email address.
     *
     * @throws ApiExceptionInterface If this field not set.
     */
    public function getEmail(): string;
    /**
     * Return customer delivery email.
     *
     * Customer delivery e-mail address. Represents email for electronic delivery for cases when
     * it's not the same as customer's email.
     *
     * @return string Customer email address for digital delivery.
     *
     * @throws ApiExceptionInterface If this field is not set.
     */
    public function getDeliveryEmail(): string;
    /**
     * Return customer phones map.
     *
     * @return array{mobile: PhoneInterface} Customer phones.
     *
     * @throws ApiExceptionInterface If this field is not set.
     */
    public function getPhones(): array;
    /**
     * Return map of customer addresses.
     *
     * @return array {
     *     billing: AddressInterface,
     *     shipping?: AddressInterface
     * } Customer addresses.
     *
     *
     * @throws ApiExceptionInterface If this field is not set.
     */
    public function getAddresses(): array;
    /**
     * Return object with customer registration data.
     *
     * @return RegistrationInterface
     *
     * @throws ApiExceptionInterface If this field is not set.
     */
    public function getRegistration(): RegistrationInterface;
    /**
     * Return object with customer name.
     *
     * @return NameInterface
     *
     * @throws ApiExceptionInterface If this field is not set.
     */
    public function getName(): NameInterface;
}
