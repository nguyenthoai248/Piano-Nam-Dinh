<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Registration;

interface RegistrationFactoryInterface
{
    /**
     * @param string $id
     * @param string|null $password
     *
     * @return RegistrationInterface
     */
    public function createRegistration(string $id, string $password = null): RegistrationInterface;
}
