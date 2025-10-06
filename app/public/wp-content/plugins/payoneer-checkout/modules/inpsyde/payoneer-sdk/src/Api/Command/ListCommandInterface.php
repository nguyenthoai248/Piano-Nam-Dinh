<?php

namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\System\SystemInterface;
interface ListCommandInterface extends PaymentCommandInterface
{
    /**
     * @param CallbackInterface $callback
     *
     * @return static
     */
    public function withCallback(CallbackInterface $callback): self;
    /**
     * @param CustomerInterface $customer
     *
     * @return static
     */
    public function withCustomer(CustomerInterface $customer): self;
    /**
     * @param string $country Country code.
     *
     * @return static
     */
    public function withCountry(string $country): self;
    /**
     * Return a new instance with provided views only.
     *
     * @param string[] $views
     *
     * @return static
     */
    public function withViews(array $views): self;
    /**
     * Return a new instance with provided views merged with those already added.
     *
     * @param array $views
     *
     * @return static
     */
    public function withAddedViews(array $views): self;
    /**
     * Return a new instance without provided views.
     *
     * @param string[] $views Views to exclude.
     *
     * @return static
     */
    public function withoutViews(array $views): self;
    /**
     * Return a new instance with provided division.
     *
     * @param string $division Division to set
     *
     * @return static
     */
    public function withDivision(string $division): self;
    /**
     * Return a new instance without division.
     *
     * @return static
     */
    public function withoutDivision(): self;
    /**
     * Return a new instance with provided system.
     *
     * @param SystemInterface $system System to set
     *
     * @return static
     */
    public function withSystem(SystemInterface $system): self;
    /**
     * Return currently configured callback.
     *
     * @return CallbackInterface|null
     */
    public function getCallback(): ?CallbackInterface;
    /**
     * Return currently configured customer.
     *
     * @return CustomerInterface|null
     */
    public function getCustomer(): ?CustomerInterface;
    /**
     * Return currently configured country.
     *
     * @return string|null
     */
    public function getCountry(): ?string;
    /**
     * Return currently configured views.
     *
     * @return array
     */
    public function getViews(): array;
    /**
     * Return currently configured division.
     *
     * @return string|null
     */
    public function getDivision(): ?string;
    /**
     * Return currently configured system.
     *
     * @return SystemInterface|null
     */
    public function getSystem(): ?SystemInterface;
}
