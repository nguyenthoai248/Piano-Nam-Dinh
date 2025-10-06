<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Address\AddressSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Name\NameSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Phone\PhoneInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Phone\PhoneSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Registration\RegistrationSerializerInterface;
class CustomerSerializer implements CustomerSerializerInterface
{
    /**
     * @var PhoneSerializerInterface
     */
    protected $phoneSerializer;
    /**
     * @var AddressSerializerInterface
     */
    protected $addressSerializer;
    /**
     * @var RegistrationSerializerInterface
     */
    protected $registrationSerializer;
    /**
     * @var NameSerializerInterface
     */
    protected $nameSerializer;
    /**
     * @param PhoneSerializerInterface $phoneSerializer To serialize customer's phones.
     * @param AddressSerializerInterface $addressSerializer To serialize customer's addresses.
     * @param RegistrationSerializerInterface $registrationSerializer To serialize customer's registration.
     * @param NameSerializerInterface $nameSerializer To serialize customer's name.
     */
    public function __construct(PhoneSerializerInterface $phoneSerializer, AddressSerializerInterface $addressSerializer, RegistrationSerializerInterface $registrationSerializer, NameSerializerInterface $nameSerializer)
    {
        $this->phoneSerializer = $phoneSerializer;
        $this->addressSerializer = $addressSerializer;
        $this->registrationSerializer = $registrationSerializer;
        $this->nameSerializer = $nameSerializer;
    }
    /**
     * @inheritDoc
     *
     * @todo: consider refactoring
     */
    // phpcs:ignore Inpsyde.CodeQuality.FunctionLength.TooLong
    public function serializeCustomer(CustomerInterface $customer): array
    {
        $serializedCustomer = [];
        try {
            $serializedCustomer['number'] = $customer->getNumber();
        } catch (ApiExceptionInterface $apiException) {
            //this is an optional field, so it's ok to not have it
        }
        try {
            $customerPhones = $customer->getPhones();
        } catch (ApiExceptionInterface $apiException) {
            //this is an optional field, so it's ok to not have it
        }
        if (isset($customerPhones)) {
            $serializedCustomer['phones'] = array_map(function (PhoneInterface $phone): array {
                return $this->phoneSerializer->serializePhone($phone);
            }, $customerPhones);
        }
        try {
            $customerAddresses = $customer->getAddresses();
        } catch (ApiExceptionInterface $apiException) {
            //this is an optional field, so it's ok to not have it
        }
        if (isset($customerAddresses)) {
            $serializedAddresses = ['billing' => $this->addressSerializer->serializeAddress($customerAddresses['billing'])];
            if (isset($customerAddresses['shipping'])) {
                $serializedAddresses['shipping'] = $this->addressSerializer->serializeAddress($customerAddresses['shipping']);
            }
            $serializedCustomer['addresses'] = $serializedAddresses;
        }
        try {
            $serializedCustomer['email'] = $customer->getEmail();
        } catch (ApiExceptionInterface $apiException) {
            //this is an optional field, so it's ok to not have it
        }
        try {
            $serializedCustomer['deliveryEmail'] = $customer->getDeliveryEmail();
        } catch (ApiExceptionInterface $apiException) {
            //this is an optional field, so it's ok to not have it
        }
        try {
            $registration = $customer->getRegistration();
            $serializedCustomer['registration'] = $this->registrationSerializer->serializeRegistration($registration);
        } catch (ApiExceptionInterface $apiException) {
            //this is an optional field, so it's ok to not have it
        }
        try {
            $name = $customer->getName();
            $serializedCustomer['name'] = $this->nameSerializer->serializeName($name);
        } catch (ApiExceptionInterface $apiException) {
            //this is an optional field, so it's ok to not have it
        }
        return $serializedCustomer;
    }
}
