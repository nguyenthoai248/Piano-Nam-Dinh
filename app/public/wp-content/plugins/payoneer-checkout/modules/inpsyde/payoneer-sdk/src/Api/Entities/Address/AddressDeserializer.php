<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Address;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Name\NameDeserializerInterface;
class AddressDeserializer implements AddressDeserializerInterface
{
    /**
     * @var AddressFactoryInterface
     */
    protected $addressFactory;
    /**
     * @var NameDeserializerInterface
     */
    protected $nameDeserializer;
    /**
     * @param AddressFactoryInterface $addressFactory To create Address instance.
     * @param NameDeserializerInterface $nameDeserializer To create Name instance for Address.
     */
    public function __construct(AddressFactoryInterface $addressFactory, NameDeserializerInterface $nameDeserializer)
    {
        $this->addressFactory = $addressFactory;
        $this->nameDeserializer = $nameDeserializer;
    }
    /**
     * @inheritDoc
     */
    public function deserializeAddress(array $addressData): AddressInterface
    {
        $name = null;
        if (isset($addressData['name'])) {
            $nameData = $addressData['name'];
            $name = $this->nameDeserializer->deserializeName($nameData);
        }
        $state = $addressData['state'] ?? null;
        $address = $this->addressFactory->createAddress($addressData['country'], $addressData['city'], $addressData['street'], $addressData['zip'], $name, $state);
        return $address;
    }
}
