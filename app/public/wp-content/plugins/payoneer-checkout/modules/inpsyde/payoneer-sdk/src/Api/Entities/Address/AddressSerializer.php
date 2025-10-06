<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Address;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Name\NameSerializerInterface;
class AddressSerializer implements AddressSerializerInterface
{
    /**
     * @var NameSerializerInterface
     */
    protected $nameSerializer;
    /**
     * @param NameSerializerInterface $nameSerializer To serialize a name from Address instance.
     */
    public function __construct(NameSerializerInterface $nameSerializer)
    {
        $this->nameSerializer = $nameSerializer;
    }
    /**
     * @inheritDoc
     */
    public function serializeAddress(AddressInterface $address): array
    {
        try {
            $name = $address->getName();
        } catch (ApiExceptionInterface $apiException) {
            // Name is optional field in the address, so it's ok to not have it here.
        }
        try {
            $state = $address->getState();
        } catch (ApiExceptionInterface $apiException) {
            // State is the optional field in the address, so it's ok to not have it here.
        }
        $serializedAddress = ['country' => $address->getCountry(), 'city' => $address->getCity(), 'street' => $address->getStreet(), 'zip' => $address->getPostalCode()];
        if (isset($name)) {
            $serializedName = $this->nameSerializer->serializeName($name);
            $serializedAddress['name'] = $serializedName;
        }
        if (isset($state)) {
            $serializedAddress['state'] = $state;
        }
        return $serializedAddress;
    }
}
