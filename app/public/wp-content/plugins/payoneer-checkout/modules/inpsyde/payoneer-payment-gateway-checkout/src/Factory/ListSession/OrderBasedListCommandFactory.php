<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\Factory\ListSession;

use Inpsyde\PayoneerForWoocommerce\Checkout\TransactionIdGenerator\TransactionIdGeneratorInterface;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Callback\WcOrderBasedCallbackFactoryInterface;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Customer\WcOrderBasedCustomerFactoryInterface;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Payment\WcOrderBasedPaymentFactoryInterface;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Product\WcOrderBasedProductsFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Command\CreateListCommandInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Style\StyleFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\System\SystemInterface;
use Inpsyde\PayoneerSdk\Api\PayoneerIntegrationTypes;
use Inpsyde\PayoneerSdk\Api\PayoneerInterface;

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

    public function __construct(
        PayoneerInterface $payoneer,
        TransactionIdGeneratorInterface $transactionIdGenerator,
        WcOrderBasedCallbackFactoryInterface $callbackFactory,
        WcOrderBasedCustomerFactoryInterface $customerFactory,
        WcOrderBasedPaymentFactoryInterface $paymentFactory,
        StyleFactoryInterface $styleFactory,
        WcOrderBasedProductsFactoryInterface $wcOrderBasedProductsFactory,
        SystemInterface $system,
        string $locale,
        ?string $division
    ) {

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
    }

    public function createListCommand(\WC_Order $order): CreateListCommandInterface
    {
        $command = $this->payoneer->getListCommand();
        $transactionId = $this->transactionIdGenerator->generateTransactionId();

        $command = $command->withTransactionId($transactionId)
                           ->withCountry(
                               $order->get_shipping_country() ?: $order->get_billing_country()
                           )
                           ->withCallback($this->callbackFactory->createCallback($order))
                           ->withCustomer($this->customerFactory->createCustomer($order))
                           ->withPayment($this->paymentFactory->createPayment($order))
                           ->withStyle($this->styleFactory->createStyle($this->locale))
                           ->withSystem($this->system)->withIntegrationType(
                               PayoneerIntegrationTypes::SELECTIVE_NATIVE
                           )
                           ->withProducts(
                               $this->wcOrderBasedProductsFactory->createProductsFromWcOrder($order)
                           );
        if (is_string($this->division)) {
            $command = $command->withDivision($this->division);
        }

        return $command;
    }
}
