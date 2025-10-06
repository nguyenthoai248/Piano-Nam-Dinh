<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Identification;

class IdentificationFactory implements IdentificationFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createIdentification(string $longId, string $shortId, string $transactionId, string $pspId): IdentificationInterface
    {
        return new Identification($longId, $shortId, $transactionId, $pspId);
    }
}
