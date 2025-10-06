<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Phone;

/**
 * A service able to convert array to Phone instance.
 */
interface PhoneDeserializerInterface
{
    /**
     * Convert array into Phone instance.
     *
     * @param array{unstructuredNumber: string} $phoneData Data to create Phone from.
     *
     * @return PhoneInterface Created phone instance.
     */
    public function deserializePhone(array $phoneData): PhoneInterface;
}
