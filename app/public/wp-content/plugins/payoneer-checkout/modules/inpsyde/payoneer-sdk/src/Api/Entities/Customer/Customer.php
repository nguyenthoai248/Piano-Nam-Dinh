<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiException;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Address\AddressInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Name\NameInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Phone\PhoneInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Registration\RegistrationInterface;
class Customer implements CustomerInterface
{
    /**
     * Customer identifier given by merchant.
     *
     * @var string|null
     */
    protected $number;
    /**
     * Customer email address.
     *
     * @var string|null
     */
    protected $email;
    /**
     * Customer email address to send digital products.
     *
     * @var string|null
     */
    protected $deliveryEmail;
    /**
     * Customer phones.
     *
     * @var array{mobile: PhoneInterface}|null
     */
    protected $phones;
    /**
     * @var array|null
     */
    protected $addresses;
    /**
     * @var RegistrationInterface|null
     */
    protected $registration;
    /**
     * @var NameInterface|null
     */
    protected $name;
    /**
     * @param string $number Customer identifier given by merchant.
     * @param array{mobile: PhoneInterface}|null $phones Customer phones.
     * @param array{billing: AddressInterface, shipping?: AddressInterface}|null $addresses Addresses.
     * @param string|null $email Customer email.
     * @param string|null $deliveryEmail Customer email for digital products delivery.
     * @param RegistrationInterface|null $registration Object with info about customer registration in the Payoneer API.
     * @param NameInterface|null $name Object containing customer name.
     */
    public function __construct(string $number = null, array $phones = null, array $addresses = null, string $email = null, string $deliveryEmail = null, RegistrationInterface $registration = null, NameInterface $name = null)
    {
        $this->number = $number;
        $this->phones = $phones;
        $this->email = $email;
        $this->deliveryEmail = $deliveryEmail;
        $this->addresses = $addresses;
        $this->registration = $registration;
        $this->name = $name;
    }
    /**
     * @inheritDoc
     */
    public function getNumber(): string
    {
        if ($this->number === null) {
            throw new ApiException('number field is not set');
        }
        return $this->number;
    }
    /**
     * @inheritDoc
     */
    public function getEmail(): string
    {
        if ($this->email === null) {
            throw new ApiException('email field is not set.');
        }
        return $this->email;
    }
    /**
     * @inheritDoc
     */
    public function getDeliveryEmail(): string
    {
        if ($this->deliveryEmail === null) {
            throw new ApiException('deliveryEmail field is not set.');
        }
        return $this->deliveryEmail;
    }
    /**
     * @inheritDoc
     */
    public function getPhones(): array
    {
        if ($this->phones === null) {
            throw new ApiException('phones field is not set.');
        }
        return $this->phones;
    }
    /**
     * @inheritDoc
     */
    public function getAddresses(): array
    {
        if ($this->addresses === null) {
            throw new ApiException('addresses field is not set.');
        }
        return $this->addresses;
    }
    /**
     * @inheritDoc
     */
    public function getRegistration(): RegistrationInterface
    {
        if ($this->registration === null) {
            throw new ApiException('registration field is not set.');
        }
        return $this->registration;
    }
    /**
     * @inheritDoc
     */
    public function getName(): NameInterface
    {
        if ($this->name === null) {
            throw new ApiException('name field is not set');
        }
        return $this->name;
    }
}
