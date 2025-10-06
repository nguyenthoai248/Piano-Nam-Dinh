<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Registration;

interface RegistrationDeserializerInterface
{
    /**
     * @param array{id: string, password?: string} $registrationData
     *
     * @retun RegistrationInterface
     */
    public function deserializeRegistration(array $registrationData): RegistrationInterface;
}
