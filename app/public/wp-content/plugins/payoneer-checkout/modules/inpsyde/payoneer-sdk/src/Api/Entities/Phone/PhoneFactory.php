<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Phone;

class PhoneFactory implements PhoneFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createPhone(string $unstructuredNumber): PhoneInterface
    {
        return new Phone($unstructuredNumber);
    }
}
