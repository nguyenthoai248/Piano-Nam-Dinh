<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Registration;

interface RegistrationSerializerInterface
{
    /**
     * @param RegistrationInterface $registration
     *
     * @return array{id: string, password?: string}
     */
    public function serializeRegistration(RegistrationInterface $registration): array;
}
