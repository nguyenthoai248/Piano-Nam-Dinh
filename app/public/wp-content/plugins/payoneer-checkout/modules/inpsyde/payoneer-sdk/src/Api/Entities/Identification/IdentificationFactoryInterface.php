<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Identification;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
/**
 * Service able to create new Identification instance.
 */
interface IdentificationFactoryInterface
{
    /**
     * Create a new Identification object.
     *
     * @param string $longId Unique session identifier.
     * @param string $shortId Short identifier, may not be unique.
     * @param string $transactionId Transaction id created by merchant.
     * @param string $pspId Transaction id created by Payment Service Provider.
     *
     * @return IdentificationInterface Created identification object
     *
     * @throws ApiExceptionInterface If failed to create.
     */
    public function createIdentification(string $longId, string $shortId, string $transactionId, string $pspId): IdentificationInterface;
}
