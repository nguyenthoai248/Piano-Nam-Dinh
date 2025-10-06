<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\ListSession;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\TransactionIdGenerator\TransactionIdGeneratorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\Customer\WcBasedCustomerFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\FactoryException;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\Product\WcCartBasedProductListFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\UpdateListCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Identification\IdentificationInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Style\StyleFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\System\SystemInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\PayoneerInterface;
use Syde\Vendor\Psr\Http\Message\UriInterface;
use WC_Cart;
use WC_Customer;
class WcBasedListSessionFactory implements WcBasedListSessionFactoryInterface, WcBasedUpdateCommandFactoryInterface
{
    /**
     * @var PayoneerInterface
     */
    protected $payoneer;
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
    protected string $fallbackCountry;
    public function __construct(PayoneerInterface $payoneer, CallbackFactoryInterface $callbackFactory, PaymentFactoryInterface $paymentFactory, StyleFactoryInterface $styleFactory, WcBasedCustomerFactoryInterface $customerFactory, WcCartBasedProductListFactoryInterface $productListFactory, UriInterface $notificationUrl, string $checkoutLanguage, string $currency, SystemInterface $system, TransactionIdGeneratorInterface $transactionIdGenerator, string $division, string $fallbackCountry)
    {
        $this->payoneer = $payoneer;
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
        $this->fallbackCountry = $fallbackCountry;
    }
    public function createList(WC_Customer $customer, WC_Cart $cart, string $integrationType, string $hostedVersion = null): ListInterface
    {
        $transactionId = $this->transactionIdGenerator->generateTransactionId();
        $country = ($customer->get_billing_country() ?: $customer->get_shipping_country()) ?: $this->fallbackCountry;
        $callback = $this->createCallback((string) $this->notificationUrl);
        $customer = $this->customerFactory->createCustomerFromWcCustomer($customer);
        $style = $this->styleFactory->createStyle($this->checkoutLanguage);
        if ($hostedVersion) {
            $style = $style->withHostedVersion($hostedVersion);
        }
        $products = $this->productListFactory->createProductListFromWcCart($cart);
        try {
            $payment = $this->createPayment($cart);
            $createListCommand = $this->payoneer->getListCommand()->withTransactionId($transactionId)->withCallback($callback)->withCustomer($customer)->withPayment($payment)->withStyle($style)->withOperationType('CHARGE')->withProducts($products)->withSystem($this->system)->withIntegrationType($integrationType)->withDivision($this->division);
            if ($country) {
                $createListCommand = $createListCommand->withCountry($country);
            }
            do_action('payoneer-checkout.before_create_list');
            $list = $createListCommand->execute();
            do_action('payoneer-checkout.list_session_created', ['longId' => $list->getIdentification()->getLongId(), 'list' => $list]);
            return $list;
        } catch (ApiExceptionInterface $exception) {
            do_action('payoneer-checkout.create_list_session_failed', ['exception' => $exception]);
            throw new FactoryException('Could not create LIST session', 0, $exception);
        }
    }
    public function createUpdateCommand(IdentificationInterface $listSessionIdentification, WC_Customer $wcCustomer, WC_Cart $cart): UpdateListCommandInterface
    {
        $customer = $this->customerFactory->createCustomerFromWcCustomer($wcCustomer);
        try {
            $payment = $this->createPayment($cart);
            $callback = $this->createCallback((string) $this->notificationUrl);
            $products = $this->productListFactory->createProductListFromWcCart($cart);
            $updateCommand = $this->payoneer->getUpdateCommand()->withCustomer($customer)->withPayment($payment)->withCallback($callback)->withLongId($listSessionIdentification->getLongId())->withSystem($this->system)->withProducts($products)->withTransactionId($listSessionIdentification->getTransactionId());
            $country = ($wcCustomer->get_billing_country() ?: $wcCustomer->get_shipping_country()) ?: $this->fallbackCountry;
            if ($country) {
                $updateCommand = $updateCommand->withCountry($country);
            }
            return $updateCommand;
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
        return $this->paymentFactory->createPayment('Checkout payment', $totalAmount, $taxAmount, $netAmount, $this->currency);
    }
    /**
     * Create a Payoneer API callback instance.
     *
     * @return CallbackInterface Created Callback instance.
     */
    protected function createCallback(string $notificationUrl): CallbackInterface
    {
        $shopUrl = get_permalink(wc_get_page_id('shop')) ?: get_site_url();
        $checkoutUrl = wc_get_checkout_url();
        return $this->callbackFactory->createCallback(
            $shopUrl,
            //We have no Thank you page URL until an order is created.
            $checkoutUrl,
            $checkoutUrl,
            $notificationUrl
        );
    }
}
