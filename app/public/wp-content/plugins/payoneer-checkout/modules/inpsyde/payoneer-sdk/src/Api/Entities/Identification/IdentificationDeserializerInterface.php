<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Identification;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
/**
 * Service able to create an Identification object from array.
 */
interface IdentificationDeserializerInterface
{
    /**
     * @param array{longId: string, shortId: string, transactionId: string} $identificationData Map of session identifiers.
     *
     * @return IdentificationInterface Created identification object.
     *
     * @throws ApiExceptionInterface If failed to create an identification object.
     */
    public function deserializeIdentification(array $identificationData): IdentificationInterface;
}
