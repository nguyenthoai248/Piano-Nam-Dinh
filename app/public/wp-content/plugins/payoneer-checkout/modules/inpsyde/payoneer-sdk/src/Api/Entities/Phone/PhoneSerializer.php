<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Phone;

class PhoneSerializer implements PhoneSerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serializePhone(PhoneInterface $phone): array
    {
        $unstructuredNumber = $phone->getUnstructuredNumber();
        return ['unstructuredNumber' => $unstructuredNumber];
    }
}
