<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Identification;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
class IdentificationSerializer implements IdentificationSerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serializeIdentification(IdentificationInterface $identification): array
    {
        $serializedIdentification = ['longId' => $identification->getLongId(), 'shortId' => $identification->getShortId(), 'transactionId' => $identification->getTransactionId()];
        try {
            $serializedIdentification['pspId'] = $identification->getPspId();
        } catch (ApiExceptionInterface $apiException) {
        }
        return $serializedIdentification;
    }
}
