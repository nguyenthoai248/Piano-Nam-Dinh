<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Name;

class Name implements NameInterface
{
    /**
     * @var string
     */
    protected $firstName;
    /**
     * @var string
     */
    protected $lastName;
    /**
     * @param string $firstName
     * @param string $lastName
     */
    public function __construct(string $firstName, string $lastName)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }
    /**
     * Return customer's first name.
     *
     * @return string First name.
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }
    /**
     * Return customer's last name.
     *
     * @return string LastName.
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }
}
