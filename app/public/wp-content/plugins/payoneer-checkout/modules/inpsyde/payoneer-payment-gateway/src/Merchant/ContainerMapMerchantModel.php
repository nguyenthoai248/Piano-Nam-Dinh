<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant;

use Syde\Vendor\Dhii\Collection\MutableContainerInterface;
/**
 * Can persist a Merchant to list of merchants stored as a map in a mutable container.
 *
 */
class ContainerMapMerchantModel implements SaveMerchantCommandInterface, MerchantQueryInterface
{
    use ContainerMapSaveMerchantTrait;
    use ContainerMapMerchantQueryTrait;
    public function __construct(MutableContainerInterface $storage, string $storageKey, MerchantSerializerInterface $serializer, MerchantDeserializerInterface $deserializer)
    {
        $this->storage = $storage;
        $this->storageKey = $storageKey;
        $this->serializer = $serializer;
        $this->deserializer = $deserializer;
    }
}
