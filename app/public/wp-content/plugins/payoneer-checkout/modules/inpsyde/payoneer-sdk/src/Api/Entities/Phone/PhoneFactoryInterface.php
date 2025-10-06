<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Phone;

/**
 * Service able to create Phone instance.
 */
interface PhoneFactoryInterface
{
    /**
     * Create a new phone instance.
     *
     * @param string $unstructuredNumber Phone number as a string.
     *
     * @return PhoneInterface Created object.
     */
    public function createPhone(string $unstructuredNumber): PhoneInterface;
}
