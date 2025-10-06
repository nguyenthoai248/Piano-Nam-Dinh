<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Registration;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
class RegistrationSerializer implements RegistrationSerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serializeRegistration(RegistrationInterface $registration): array
    {
        $serializedRegistration = ['id' => $registration->getId()];
        try {
            $serializedRegistration['password'] = $registration->getPassword();
        } catch (ApiExceptionInterface $exception) {
            //password field is optional, do nothing here
        }
        return $serializedRegistration;
    }
}
