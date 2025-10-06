<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Registration;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
interface RegistrationInterface
{
    /**
     * Get registration ID.
     *
     * @return string
     */
    public function getId(): string;
    /**
     * Get registration password.
     *
     * @return string
     *
     * @throws ApiExceptionInterface
     */
    public function getPassword(): string;
}
