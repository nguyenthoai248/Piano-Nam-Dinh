<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\Factory\Customer;

use Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutExceptionInterface;
use Inpsyde\PayoneerForWoocommerce\Checkout\Factory\FactoryException;
use Inpsyde\PayoneerForWoocommerce\Checkout\StateProvider\StateProviderInterface;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Customer\WcOrderBasedCustomerFactory;
use Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Address\AddressFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Address\AddressInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Name\NameFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Phone\PhoneFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Registration\RegistrationFactoryInterface;
use UnexpectedValueException;
use WC_Customer;

class WcBasedCustomerFactory implements WcBasedCustomerFactoryInterface
{
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
    public function __construct(
        CustomerFactoryInterface $customerFactory,
        PhoneFactoryInterface $phoneFactory,
        AddressFactoryInterface $addressFactory,
        NameFactoryInterface $nameFactory,
        RegistrationFactoryInterface $registrationFactory,
        string $registrationIdFieldName,
        StateProviderInterface $stateProvider
    ) {

        $this->customerFactory = $customerFactory;
        $this->phoneFactory = $phoneFactory;
        $this->addressFactory = $addressFactory;
        $this->nameFactory = $nameFactory;
        $this->registrationFactory = $registrationFactory;
        $this->registrationIdFieldName = $registrationIdFieldName;
        $this->stateProvider = $stateProvider;
    }

    /**
     * @inheritDoc
     * @psalm-suppress UnusedVariable
     */
    public function createCustomerFromWcCustomer(WC_Customer $wcCustomer): CustomerInterface
    {
        $customerNumber = (string) $wcCustomer->get_id();

        /** @var mixed $customerPhone */
        $customerPhone = $wcCustomer->get_billing_phone();

        $phones = null;

        if ($customerPhone) {
            $mobilePhone = $this->phoneFactory->createPhone((string) $customerPhone);
            $phones = [
                'mobile' => $mobilePhone,
            ];
        }

        $email = $wcCustomer->get_billing_email();

        try {
            $addresses = $this->createAddresses($wcCustomer);
        } catch (UnexpectedValueException $exception) {
            $addresses = null;
        }

        $registrationId = (string) $wcCustomer->get_meta($this->registrationIdFieldName, true);
        $registration = null;

        if (! empty($registrationId)) {
            $registration = $this->registrationFactory
                ->createRegistration($registrationId);
        }

        $name = $this->nameFactory->createName(
            $wcCustomer->get_billing_first_name(),
            $wcCustomer->get_billing_last_name()
        );

        try {
            $customer = $this->customerFactory->createCustomer(
                $customerNumber,
                $phones,
                $addresses,
                $email,
                null,
                null, //TODO pass $registration once the management UI is available
                $name
            );
        } catch (ApiExceptionInterface $exception) {
            throw new FactoryException(
                sprintf(
                    'Failed to transform WC Customer into Payoneer SDK Customer. Exception caught: %1$s',
                    $exception->getMessage()
                ),
                0,
                $exception
            );
        }

        return $customer;
    }

    /**
     * Create a billing address from WC_Customer.
     *
     * @param WC_Customer $wcCustomer
     *
     * @return AddressInterface
     */
    protected function createCustomerBillingAddress(WC_Customer $wcCustomer): AddressInterface
    {
        /** @var mixed $country */
        $country = $wcCustomer->get_billing_country();
        /** @var mixed $city */
        $city = $wcCustomer->get_billing_city();
        /** @var mixed $street */
        $street = $wcCustomer->get_billing_address();
        /** @var mixed $postalCode */
        $postalCode = $this->getBillingPostcodeOrFallback($wcCustomer);
        /** @var mixed $firstName */
        $firstName = $wcCustomer->get_billing_first_name();
        /** @var mixed $lastName */
        $lastName = $wcCustomer->get_billing_last_name();
        /** @var mixed $billingStateCode */
        $billingStateCode = $wcCustomer->get_billing_state();

        $name = $this->nameFactory->createName(
            (string) $firstName,
            (string) $lastName
        );

        try {
            $billingState = $this->stateProvider->provideStateNameByCountryAndStateCode(
                (string) $country,
                (string) $billingStateCode
            );
        } catch (CheckoutExceptionInterface $exception) {
            $billingState = (string) $billingStateCode;
            $billingState = $billingState === '' ? null : $billingState;
        }

        $billingAddress = $this->addressFactory->createAddress(
            (string) $country,
            (string) $city,
            (string) $street,
            (string) $postalCode,
            $name,
            $billingState
        );

        return $billingAddress;
    }

    /**
     * Create a shipping address from WC_Customer.
     *
     * @param WC_Customer $wcCustomer Customer to get data from.
     *
     * @return AddressInterface Created address.
     */
    public function createCustomerShippingAddress(WC_Customer $wcCustomer): AddressInterface
    {
        /** @var mixed $country */
        $country = $wcCustomer->get_shipping_country();
        /** @var mixed $city */
        $city = $wcCustomer->get_shipping_city();
        /** @var mixed $street */
        $street = $wcCustomer->get_shipping_address();
        /** @var mixed $postalCode */
        $postalCode = $this->getShippingPostcodeOrFallback($wcCustomer);
        /** @var mixed $firstName */
        $firstName = $wcCustomer->get_shipping_first_name();
        /** @var mixed $lastName */
        $lastName = $wcCustomer->get_shipping_last_name();
        /** @var mixed $shippingStateCode */
        $shippingStateCode = $wcCustomer->get_shipping_state();

        $name = $this->nameFactory->createName((string) $firstName, (string) $lastName);

        try {
            $shippingState = $this->stateProvider->provideStateNameByCountryAndStateCode(
                (string) $country,
                (string) $shippingStateCode
            );
        } catch (CheckoutExceptionInterface $exception) {
            $shippingState = (string) $shippingStateCode;
            $shippingState = $shippingState === '' ? null : $shippingState;
        }

        $shippingAddress = $this->addressFactory->createAddress(
            (string) $country,
            (string) $city,
            (string) $street,
            (string) $postalCode,
            $name,
            $shippingState
        );

        return $shippingAddress;
    }

    /**
     * Return the billing postcode from the customer or fallback in case is missing
     *
     * @param WC_Customer $wcCustomer
     *
     * @return string
     */
    protected function getBillingPostcodeOrFallback(WC_Customer $wcCustomer): string
    {
        return !empty($wcCustomer->get_billing_postcode()) ? $wcCustomer->get_billing_postcode() : WcOrderBasedCustomerFactory::FALLBACK_POSTCODE;
    }

    /**
     * Return the shipping postcode from the customer or fallback in case is missing
     *
     * @param WC_Customer $wcCustomer
     *
     * @return string
     */
    protected function getShippingPostcodeOrFallback(WC_Customer $wcCustomer): string
    {
        return !empty($wcCustomer->get_shipping_postcode()) ? $wcCustomer->get_shipping_postcode() : WcOrderBasedCustomerFactory::FALLBACK_POSTCODE;
    }

    /**
     * Create an array of billing and shipping addresses from WC_Customer instance.
     *
     * @param WC_Customer $wcCustomer WC customer containing data.
     *
     * @return array{billing: AddressInterface, shipping: AddressInterface}
     *
     * @throws UnexpectedValueException If provided WC customer has no billing country.
     */
    protected function createAddresses(WC_Customer $wcCustomer): array
    {
        if (! $wcCustomer->get_billing_country()) {
            throw new UnexpectedValueException(
                'Cannot create addresses: WC customer has no billing country set.'
            );
        }
        $addresses = [];
        $addresses['billing'] = $this->createCustomerBillingAddress($wcCustomer);
        $addresses['shipping'] = $wcCustomer->get_shipping_country() ?
            $this->createCustomerShippingAddress($wcCustomer) :
            $addresses['billing'];

        return  $addresses;
    }
}
