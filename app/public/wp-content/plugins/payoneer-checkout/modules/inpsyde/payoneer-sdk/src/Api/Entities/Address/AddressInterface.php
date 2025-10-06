<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Address;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Name\NameInterface;
/**
 * Represent customer address.
 */
interface AddressInterface
{
    /**
     * Return country name.
     *
     * @return string Country name.
     */
    public function getCountry(): string;
    /**
     * Return city name.
     *
     * @return string City name.
     */
    public function getCity(): string;
    /**
     * Return street name.
     *
     * @return string Street name.
     */
    public function getStreet(): string;
    /**
     * Return Postal code
     *
     * @return string Postal code.
     */
    public function getPostalCode(): string;
    /**
     * Return object containing customer's full name.
     *
     * @return NameInterface Customer full name.
     *
     * @throws ApiExceptionInterface If no name set.
     */
    public function getName(): NameInterface;
    /**
     * @return string Customer address state
     *
     * @throws ApiExceptionInterface If no state set.
     */
    public function getState(): string;
}
