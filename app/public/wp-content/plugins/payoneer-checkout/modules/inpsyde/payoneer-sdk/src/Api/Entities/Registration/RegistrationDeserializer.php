<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Registration;

class RegistrationDeserializer implements RegistrationDeserializerInterface
{
    /**
     * @var RegistrationFactoryInterface
     */
    protected $registrationFactory;
    /**
     * @param RegistrationFactoryInterface $registrationFactory
     */
    public function __construct(RegistrationFactoryInterface $registrationFactory)
    {
        $this->registrationFactory = $registrationFactory;
    }
    /**
     * @inheritDoc
     */
    public function deserializeRegistration(array $registrationData): RegistrationInterface
    {
        return $this->registrationFactory->createRegistration($registrationData['id'], $registrationData['password'] ?? null);
    }
}
