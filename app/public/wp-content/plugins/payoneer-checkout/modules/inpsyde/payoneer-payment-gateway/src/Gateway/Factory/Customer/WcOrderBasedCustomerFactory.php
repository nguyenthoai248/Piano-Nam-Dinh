<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Customer;

use Exception;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\StateProvider\StateProviderInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Address\AddressFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Address\AddressInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Name\NameFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Phone\PhoneFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Phone\PhoneInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Registration\RegistrationFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Registration\RegistrationInterface;
use UnexpectedValueException;
use WC_Order;
class WcOrderBasedCustomerFactory implements WcOrderBasedCustomerFactoryInterface
{
    public const FALLBACK_POSTCODE = '00000';
    /**
     * @var CustomerFactoryInterface
     */
    protected $customerFactory;
    /**
     * @var PhoneFactoryInterface
     */
    protected $phoneFactory;
    /**
     * @var AddressFactoryInterface
     */
    protected $addressFactory;
    /**
     * @var NameFactoryInterface
     */
    protected $nameFactory;
    /**
     * @var RegistrationFactoryInterface
     */
    protected $registrationFactory;
    /**
     * @var string
     */
    protected $registrationIdFieldName;
    /**
     * @var StateProviderInterface
     */
    protected $stateProvider;
    /**
     * @param CustomerFactoryInterface $customerFactory
     * @param PhoneFactoryInterface $phoneFactory
     * @param AddressFactoryInterface $addressFactory
     * @param NameFactoryInterface $nameFactory
     * @param RegistrationFactoryInterface $registrationFactory
     * @param string $registrationIdFieldName
     * @param StateProviderInterface $stateProvider
     */
    public function __construct(CustomerFactoryInterface $customerFactory, PhoneFactoryInterface $phoneFactory, AddressFactoryInterface $addressFactory, NameFactoryInterface $nameFactory, RegistrationFactoryInterface $registrationFactory, string $registrationIdFieldName, StateProviderInterface $stateProvider)
    {
        $this->customerFactory = $customerFactory;
        $this->phoneFactory = $phoneFactory;
        $this->addressFactory = $addressFactory;
        $this->nameFactory = $nameFactory;
        $this->registrationFactory = $registrationFactory;
        $this->registrationIdFieldName = $registrationIdFieldName;
        $this->stateProvider = $stateProvider;
    }
    /**
     * @param WC_Order $order
     *
     * @return CustomerInterface
     * @throws \Inpsyde\PayoneerSdk\Api\ApiExceptionInterface
     * @psalm-suppress UnusedVariable
     */
    public function createCustomer(WC_Order $order) : CustomerInterface
    {
        $phones = $this->createPhoneArrayFromOrder($order);
        $registration = $this->createRegistrationFromOrder($order);
        try {
            $addresses = $this->createAddresses($order);
        } catch (UnexpectedValueException $exception) {
            $addresses = null;
        }
        $name = $this->nameFactory->createName($order->get_billing_first_name(), $order->get_billing_last_name());
        $customer = $this->customerFactory->createCustomer(
            (string) $order->get_customer_id(),
            $phones,
            $addresses,
            $order->get_billing_email(),
            null,
            null,
            //TODO pass $registration once the management UI is available
            $name
        );
        return $customer;
    }
    /**
     * @param WC_Order $order
     *
     * @return AddressInterface
     */
    protected function createBillingAddressFromOrder(WC_Order $order) : AddressInterface
    {
        $name = $this->nameFactory->createName($order->get_billing_first_name(), $order->get_billing_last_name());
        /** @var mixed $billingStateCode */
        $billingStateCode = $order->get_billing_state();
        try {
            $billingState = $this->stateProvider->provideStateNameByCountryAndStateCode($order->get_billing_country(), (string) $billingStateCode);
        } catch (CheckoutExceptionInterface $exception) {
            $billingState = (string) $billingStateCode;
            $billingState = $billingState === '' ? null : $billingState;
        }
        $address = $this->addressFactory->createAddress($order->get_billing_country(), $order->get_billing_city(), $order->get_billing_address_1(), $this->getBillingPostcodeOrFallback($order), $name, $billingState);
        return $address;
    }
    /**
     * @param WC_Order $order
     *
     * @return AddressInterface
     */
    protected function createShippingAddressFromOrder(WC_Order $order) : AddressInterface
    {
        $name = $this->nameFactory->createName($order->get_shipping_first_name(), $order->get_shipping_last_name());
        /** @var mixed $shippingStateCode */
        $shippingStateCode = $order->get_shipping_state();
        try {
            $shippingState = $this->stateProvider->provideStateNameByCountryAndStateCode($order->get_shipping_country(), (string) $shippingStateCode);
        } catch (CheckoutExceptionInterface $exception) {
            $shippingState = (string) $shippingStateCode;
            $shippingState = $shippingState === '' ? null : $shippingState;
        }
        $address = $this->addressFactory->createAddress($order->get_shipping_country(), $order->get_shipping_city(), $order->get_shipping_address_1(), $this->getShippingPostcodeOrFallback($order), $name, $shippingState);
        return $address;
    }
    /**
     * Create phone array from the order instance if it contains phone, return null otherwise.
     *
     * @param WC_Order $order Order to get phone number from.
     *
     * @return array{mobile: PhoneInterface}|null Either phone array or null.
     */
    protected function createPhoneArrayFromOrder(WC_Order $order) : ?array
    {
        $phone = $order->get_billing_phone();
        if ($phone) {
            $phone = $this->phoneFactory->createPhone($phone);
        }
        return $phone instanceof PhoneInterface ? ['mobile' => $phone] : null;
    }
    /**
     * Create a Registration instance if order customer registration data was saved before.
     *
     * @param WC_Order $order
     *
     * @return RegistrationInterface|null
     */
    protected function createRegistrationFromOrder(WC_Order $order) : ?RegistrationInterface
    {
        $wcCustomerId = $order->get_customer_id();
        try {
            $wcCustomer = new \WC_Customer($wcCustomerId);
        } catch (Exception $exception) {
            return null;
        }
        $registrationId = (string) $wcCustomer->get_meta($this->registrationIdFieldName, \true);
        if (empty($registrationId)) {
            return null;
        }
        return $this->registrationFactory->createRegistration($registrationId);
    }
    /**
     * Return the billing postcode from the order or fallback in case is missing
     *
     * @param WC_Order $order
     *
     * @return string
     */
    protected function getBillingPostcodeOrFallback(WC_Order $order) : string
    {
        return !empty($order->get_billing_postcode()) ? $order->get_billing_postcode() : self::FALLBACK_POSTCODE;
    }
    /**
     * Return the shipping postcode from the order or fallback in case is missing
     *
     * @param WC_Order $order
     *
     * @return string
     */
    protected function getShippingPostcodeOrFallback(WC_Order $order) : string
    {
        return !empty($order->get_shipping_postcode()) ? $order->get_shipping_postcode() : self::FALLBACK_POSTCODE;
    }
    /**
     * Create billing and shipping addresses from WC_Order instance.
     *
     * @param WC_Order $order Order containing data.
     *
     * @return array{billing: AddressInterface, shipping: AddressInterface}
     *
     * @throws UnexpectedValueException If order has no billing country.
     */
    protected function createAddresses(WC_Order $order) : array
    {
        if (!$order->get_billing_country()) {
            throw new UnexpectedValueException('Cannot create customer addresses: no billing country set');
        }
        $billingAddress = $this->createBillingAddressFromOrder($order);
        $shippingAddress = $order->has_shipping_address() ? $this->createShippingAddressFromOrder($order) : $billingAddress;
        return ['billing' => $billingAddress, 'shipping' => $shippingAddress];
    }
}
