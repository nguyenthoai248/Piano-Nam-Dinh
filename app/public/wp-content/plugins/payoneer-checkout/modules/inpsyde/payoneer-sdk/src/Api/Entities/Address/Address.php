<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Address;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiException;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Name\NameInterface;
class Address implements AddressInterface
{
    /**
     * @var string
     */
    protected $city;
    /**
     * @var string
     */
    protected $street;
    /**
     * @var string
     */
    protected $country;
    /**
     * @var string
     */
    protected $postalCode;
    /**
     * @var NameInterface|null
     */
    protected $name;
    /**
     * @var string|null
     */
    private $state;
    /**
     * @param string $country
     * @param string $city
     * @param string $street
     * @param string $postalCode
     * @param NameInterface|null $name
     * @param string|null $state
     */
    public function __construct(string $country, string $city, string $street, string $postalCode, NameInterface $name = null, string $state = null)
    {
        $this->country = $country;
        $this->city = $city;
        $this->street = $street;
        $this->postalCode = $postalCode;
        $this->name = $name;
        $this->state = $state;
    }
    /**
     * @inheritDoc
     */
    public function getCountry(): string
    {
        return $this->country;
    }
    /**
     * @inheritDoc
     */
    public function getCity(): string
    {
        return $this->city;
    }
    /**
     * @inheritDoc
     */
    public function getStreet(): string
    {
        return $this->street;
    }
    /**
     * @inheritDoc
     */
    public function getName(): NameInterface
    {
        if (!$this->name) {
            throw new ApiException('No name found in Address');
        }
        return $this->name;
    }
    /**
     * @inheritDoc
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }
    /**
     * @inheritDoc
     */
    public function getState(): string
    {
        if ($this->state === null) {
            throw new ApiException('No state found in Address');
        }
        return $this->state;
    }
}
