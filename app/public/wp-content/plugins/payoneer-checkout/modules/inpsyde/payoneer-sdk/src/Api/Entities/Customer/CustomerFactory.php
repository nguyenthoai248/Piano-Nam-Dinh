<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Name\NameInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Registration\RegistrationInterface;
class CustomerFactory implements CustomerFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createCustomer(string $number = null, array $phones = null, array $addresses = null, string $email = null, string $deliveryEmail = null, RegistrationInterface $registration = null, NameInterface $name = null): CustomerInterface
    {
        return new Customer($number, $phones, $addresses, $email, $deliveryEmail, $registration, $name);
    }
}
