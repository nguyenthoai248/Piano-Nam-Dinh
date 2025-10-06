<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Phone;

/**
 * Service able to convert Phone instance to array.
 */
interface PhoneSerializerInterface
{
    /**
     * Convert Phone instance into array.
     *
     * @param PhoneInterface $phone Phone to use as a data source.
     *
     * @return array{unstructuredNumber: string} Resulting array.
     */
    public function serializePhone(PhoneInterface $phone): array;
}
