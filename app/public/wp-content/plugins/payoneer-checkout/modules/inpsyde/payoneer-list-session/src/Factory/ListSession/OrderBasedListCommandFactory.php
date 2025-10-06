<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\ListSession;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\TransactionIdGenerator\TransactionIdGeneratorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\Factory\Callback\WcOrderBasedCallbackFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\Factory\Customer\WcOrderBasedCustomerFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\Factory\Payment\WcOrderBasedPaymentFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\Factory\Product\WcOrderBasedProductsFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\CreateListCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Style\StyleFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\System\SystemInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\PayoneerInterface;
class OrderBasedListCommandFactory implements OrderBasedListCommandFactoryInterface
{
    /**
     * @var PayoneerInterface
     */
    protected $payoneer;
    /**
     * @var TransactionIdGeneratorInterface
     */
    protected $transactionIdGenerator;
    /**
     * @var WcOrderBasedCallbackFactoryInterface
     */
    protected $callbackFactory;
    /**
     * @var WcOrderBasedCustomerFactoryInterface
     */
    protected $customerFactory;
    /**
     * @var WcOrderBasedPaymentFactoryInterface
     */
    protected $paymentFactory;
    /**
     * @var StyleFactoryInterface
     */
    protected $styleFactory;
    /**
     * @var WcOrderBasedProductsFactoryInterface
     */
    protected $wcOrderBasedProductsFactory;
    /**
     * @var string
     */
    protected $locale;
    /**
     * @var string|null
     */
    protected $division;
    /**
     * @var SystemInterface
     */
    protected $system;
    /**
     * @var string
     */
    protected string $fallbackCountry;
    public function __construct(PayoneerInterface $payoneer, TransactionIdGeneratorInterface $transactionIdGenerator, WcOrderBasedCallbackFactoryInterface $callbackFactory, WcOrderBasedCustomerFactoryInterface $customerFactory, WcOrderBasedPaymentFactoryInterface $paymentFactory, StyleFactoryInterface $styleFactory, WcOrderBasedProductsFactoryInterface $wcOrderBasedProductsFactory, SystemInterface $system, string $locale, string $fallbackCountry, ?string $division)
    {
        $this->payoneer = $payoneer;
        $this->transactionIdGenerator = $transactionIdGenerator;
        $this->callbackFactory = $callbackFactory;
        $this->customerFactory = $customerFactory;
        $this->paymentFactory = $paymentFactory;
        $this->styleFactory = $styleFactory;
        $this->wcOrderBasedProductsFactory = $wcOrderBasedProductsFactory;
        $this->locale = $locale;
        $this->division = $division;
        $this->system = $system;
        $this->fallbackCountry = $fallbackCountry;
    }
    public function createListCommand(\WC_Order $order, string $integrationType, string $hostedVersion = null): CreateListCommandInterface
    {
        $command = $this->payoneer->getListCommand();
        $transactionId = $this->transactionIdGenerator->generateTransactionId();
        $style = $this->styleFactory->createStyle($this->locale);
        if ($hostedVersion) {
            $style = $style->withHostedVersion($hostedVersion);
        }
        $command = $command->withTransactionId($transactionId)->withCountry(($order->get_billing_country() ?: $order->get_shipping_country()) ?: $this->fallbackCountry)->withCallback($this->callbackFactory->createCallback($order))->withCustomer($this->customerFactory->createCustomer($order))->withPayment($this->paymentFactory->createPayment($order))->withStyle($style)->withSystem($this->system)->withIntegrationType($integrationType)->withProducts($this->wcOrderBasedProductsFactory->createProductsFromWcOrder($order));
        if (is_string($this->division)) {
            $command = $command->withDivision($this->division);
        }
        return $command;
    }
}
