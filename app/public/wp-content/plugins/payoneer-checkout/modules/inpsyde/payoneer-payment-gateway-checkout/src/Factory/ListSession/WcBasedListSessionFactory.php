<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\Factory\ListSession;

use Inpsyde\PayoneerForWoocommerce\Checkout\Factory\Customer\WcBasedCustomerFactoryInterface;
use Inpsyde\PayoneerForWoocommerce\Checkout\Factory\FactoryException;
use Inpsyde\PayoneerForWoocommerce\Checkout\Factory\Product\WcCartBasedProductListFactoryInterface;
use Inpsyde\PayoneerForWoocommerce\Checkout\TransactionIdGenerator\TransactionIdGeneratorInterface;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\SecurityHeader\SecurityHeaderFactoryInterface;
use Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Command\UpdateListCommandInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Identification\IdentificationInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Style\StyleFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\System\SystemInterface;
use Inpsyde\PayoneerSdk\Api\PayoneerIntegrationTypes;
use Inpsyde\PayoneerSdk\Api\PayoneerInterface;
use Psr\Http\Message\UriInterface;
use WC_Cart;
use WC_Customer;

class WcBasedListSessionFactory implements WcBasedListSessionFactoryInterface, WcBasedUpdateCommandFactoryInterface
{
    /**
     * @var PayoneerInterface
     */
    protected $payoneer;
    /**
     * @var SecurityHeaderFactoryInterface
     */
    protected $securityHeaderFactory;
    /**
     * @var CallbackFactoryInterface
     */
    protected $callbackFactory;
    /**
     * @var PaymentFactoryInterface
     */
    protected $paymentFactory;
    /**
     * @var StyleFactoryInterface
     */
    protected $styleFactory;
    /**
     * @var WcBasedCustomerFactoryInterface
     */
    protected $customerFactory;
    /**
     * @var WcCartBasedProductListFactoryInterface
     */
    protected $productListFactory;
    /**
     * @var UriInterface
     */
    protected $notificationUrl;
    /**
     * @var string
     */
    protected $checkoutLanguage;
    /**
     * @var string
     */
    protected $currency;
    /**
     * @var string
     */
    protected $division;
    /**
     * @var SystemInterface
     */
    protected $system;
    /**
     * @var TransactionIdGeneratorInterface
     */
    protected $transactionIdGenerator;

    public function __construct(
        PayoneerInterface $payoneer,
        SecurityHeaderFactoryInterface $securityHeaderFactory,
        CallbackFactoryInterface $callbackFactory,
        PaymentFactoryInterface $paymentFactory,
        StyleFactoryInterface $styleFactory,
        WcBasedCustomerFactoryInterface $customerFactory,
        WcCartBasedProductListFactoryInterface $productListFactory,
        UriInterface $notificationUrl,
        string $checkoutLanguage,
        string $currency,
        SystemInterface $system,
        TransactionIdGeneratorInterface $transactionIdGenerator,
        string $division
    ) {

        $this->payoneer = $payoneer;
        $this->securityHeaderFactory = $securityHeaderFactory;
        $this->callbackFactory = $callbackFactory;
        $this->paymentFactory = $paymentFactory;
        $this->styleFactory = $styleFactory;
        $this->customerFactory = $customerFactory;
        $this->productListFactory = $productListFactory;
        $this->notificationUrl = $notificationUrl;
        $this->checkoutLanguage = $checkoutLanguage;
        $this->currency = $currency;
        $this->system = $system;
        $this->division = $division;
        $this->transactionIdGenerator = $transactionIdGenerator;
    }

    public function createList(
        WC_Customer $customer,
        WC_Cart $cart,
        string $listSecurityToken
    ): ListInterface {

        $transactionId = $this->transactionIdGenerator->generateTransactionId();
        $country = $customer->get_shipping_country() ?: $customer->get_billing_country();
        $callback = $this->createCallback(
            (string)$this->notificationUrl,
            $listSecurityToken
        );
        $customer = $this->customerFactory->createCustomerFromWcCustomer($customer);
        $style = $this->styleFactory->createStyle($this->checkoutLanguage);
        $products = $this->productListFactory->createProductListFromWcCart($cart);

        try {
            $payment = $this->createPayment($cart);

            $createListCommand = $this->payoneer
                ->getListCommand()
                ->withTransactionId($transactionId)
                ->withCallback($callback)
                ->withCustomer($customer)
                ->withPayment($payment)
                ->withStyle($style)
                ->withOperationType('CHARGE')
                ->withProducts($products)
                ->withSystem($this->system)
                ->withIntegrationType(PayoneerIntegrationTypes::SELECTIVE_NATIVE)
                ->withDivision($this->division);

            if ($country) {
                $createListCommand = $createListCommand->withCountry($country);
            }

            do_action('payoneer-checkout.before_create_list');

            $list = $createListCommand->execute();

            do_action(
                'payoneer-checkout.list_session_created',
                [
                    'longId' => $list->getIdentification()->getLongId(),
                    'list' => $list,
                ]
            );

            return $list;
        } catch (ApiExceptionInterface $exception) {
            do_action(
                'payoneer-checkout.create_list_session_failed',
                ['exception' => $exception]
            );
            throw new FactoryException('Could not create LIST session', 0, $exception);
        }
    }

    public function createUpdateCommand(
        IdentificationInterface $listSessionIdentification,
        WC_Customer $wcCustomer,
        WC_Cart $cart,
        string $listSecurityToken
    ): UpdateListCommandInterface {

        $customer = $this->customerFactory->createCustomerFromWcCustomer($wcCustomer);
        try {
            $payment = $this->createPayment($cart);
            $callback = $this->createCallback((string)$this->notificationUrl, $listSecurityToken);
            $products = $this->productListFactory
                ->createProductListFromWcCart($cart);

            return $this->payoneer->getUpdateCommand()
                                  ->withCustomer($customer)
                                  ->withPayment($payment)
                                  ->withCallback($callback)
                                  ->withCountry($wcCustomer->get_shipping_country() ?: $wcCustomer->get_billing_country())
                                  ->withLongId($listSessionIdentification->getLongId())
                                  ->withSystem($this->system)
                                  ->withProducts($products)
                                  ->withTransactionId(
                                      $listSessionIdentification->getTransactionId()
                                  );
        } catch (ApiExceptionInterface $exception) {
            throw new FactoryException('Could not create update command', 0, $exception);
        }
    }

    /**
     * Create a Payment instance using WC Cart details.
     *
     * @param WC_Cart $cart Cart to get data from.
     *
     * @return PaymentInterface Created Payment instance.
     *
     * @throws ApiExceptionInterface If failed to create Payment.
     */
    protected function createPayment(WC_Cart $cart): PaymentInterface
    {
        /**
         * WC_Cart::get_total returns string although WC declares float.
         * It will also wrap up price string in HTML unless anything passed
         * as argument except for the 'view' string.
         *
         * @psalm-suppress RedundantCastGivenDocblockType
         */
        $totalAmount = (float) $cart->get_total('');
        $taxAmount = $cart->get_total_tax();
        $netAmount = $totalAmount - $taxAmount;

        return $this->paymentFactory->createPayment(
            'Checkout payment',
            $totalAmount,
            $taxAmount,
            $netAmount,
            $this->currency
        );
    }

    /**
     * Create a Payoneer API callback instance.
     *
     * @param string $listSecurityToken The token to be used in notification headers for
     *     security reasons.
     *
     * @return CallbackInterface Created Callback instance.
     */
    protected function createCallback(
        string $notificationUrl,
        string $listSecurityToken
    ): CallbackInterface {

        $shopUrl = get_permalink(wc_get_page_id('shop')) ?: get_site_url();
        $checkoutUrl = wc_get_checkout_url();
        $listSecurityHeader = $this->securityHeaderFactory
            ->createSecurityHeader($listSecurityToken);

        return $this->callbackFactory->createCallback(
            $shopUrl, //We have no Thank you page URL until an order is created.
            $checkoutUrl,
            $checkoutUrl,
            $notificationUrl,
            [$listSecurityHeader]
        );
    }
}
