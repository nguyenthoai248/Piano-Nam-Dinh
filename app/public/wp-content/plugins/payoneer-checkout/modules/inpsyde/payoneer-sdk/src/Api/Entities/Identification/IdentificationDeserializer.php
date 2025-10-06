<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Identification;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiException;
class IdentificationDeserializer implements IdentificationDeserializerInterface
{
    /**
     * @var IdentificationFactoryInterface Service able to create an Identification instance.
     */
    protected $identificationFactory;
    /**
     * @param IdentificationFactoryInterface $identificationFactory To create an Identification object.
     */
    public function __construct(IdentificationFactoryInterface $identificationFactory)
    {
        $this->identificationFactory = $identificationFactory;
    }
    /**
     * @inheritDoc
     */
    public function deserializeIdentification(array $identificationData): IdentificationInterface
    {
        if (!isset($identificationData['longId'])) {
            throw new ApiException('Data contains no expected longId element.');
        }
        $longId = $identificationData['longId'];
        if (!isset($identificationData['shortId'])) {
            throw new ApiException('Data contains no expected shortId element.');
        }
        $shortId = $identificationData['shortId'];
        if (!isset($identificationData['transactionId'])) {
            throw new ApiException('Data contains no expected transactionId element.');
        }
        $transactionId = $identificationData['transactionId'];
        $pspId = $identificationData['pspId'] ?? '';
        return $this->identificationFactory->createIdentification($longId, $shortId, $transactionId, $pspId);
    }
}
