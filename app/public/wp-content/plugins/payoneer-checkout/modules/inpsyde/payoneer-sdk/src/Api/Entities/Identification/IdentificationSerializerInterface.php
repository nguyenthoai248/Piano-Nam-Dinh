<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Identification;

/**
 * Service able to convert IdentificationInterface instance to array.
 */
interface IdentificationSerializerInterface
{
    /**
     * @param IdentificationInterface $identification Object to use data from.
     *
     * @return array{longId: string, shortId: string, transactionId: string, pspId?: string} Resulting array.
     */
    public function serializeIdentification(IdentificationInterface $identification): array;
}
