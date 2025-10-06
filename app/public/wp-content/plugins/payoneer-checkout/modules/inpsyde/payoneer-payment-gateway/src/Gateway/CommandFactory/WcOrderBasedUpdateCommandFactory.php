<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\CommandFactory;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Callback\WcOrderBasedCallbackFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Customer\WcOrderBasedCustomerFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Payment\WcOrderBasedPaymentFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Product\WcOrderBasedProductsFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\UpdateListCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\System\SystemInterface;
use WC_Order;
class WcOrderBasedUpdateCommandFactory implements WcOrderBasedUpdateCommandFactoryInterface
{
    /**
     * @var UpdateListCommandInterface
     */
    protected $updateListCommand;
    /**
     * @var WcOrderBasedCallbackFactoryInterface
     */
    protected $callbackFactory;
    /**
     * @var WcOrderBasedCustomerFactoryInterface
     */
    protected $customerFactory;
    /**
     * @var WcOrderBasedProductsFactoryInterface
     */
    protected $productsFactory;
    /**
     * @var SystemInterface
     */
    protected $system;
    /**
     * @var WcOrderBasedPaymentFactoryInterface
     */
    protected $paymentFactory;
    /**
     * @param UpdateListCommandInterface $updateListCommand
     * @param WcOrderBasedPaymentFactoryInterface $paymentFactory
     * @param WcOrderBasedCallbackFactoryInterface $wcOrderBasedCallbackFactory
     * @param WcOrderBasedCustomerFactoryInterface $wcOrderBasedCustomerFactory
     * @param WcOrderBasedProductsFactoryInterface $productsFactory
     * @param SystemInterface $system
     */
    public function __construct(UpdateListCommandInterface $updateListCommand, WcOrderBasedPaymentFactoryInterface $paymentFactory, WcOrderBasedCallbackFactoryInterface $wcOrderBasedCallbackFactory, WcOrderBasedCustomerFactoryInterface $wcOrderBasedCustomerFactory, WcOrderBasedProductsFactoryInterface $productsFactory, SystemInterface $system)
    {
        $this->updateListCommand = $updateListCommand;
        $this->callbackFactory = $wcOrderBasedCallbackFactory;
        $this->customerFactory = $wcOrderBasedCustomerFactory;
        $this->paymentFactory = $paymentFactory;
        $this->productsFactory = $productsFactory;
        $this->system = $system;
    }
    /**
     * @inheritDoc
     */
    public function createUpdateCommand(WC_Order $order, ListInterface $list) : UpdateListCommandInterface
    {
        $payment = $this->paymentFactory->createPayment($order);
        $callback = $this->callbackFactory->createCallback($order);
        $customer = $this->customerFactory->createCustomer($order);
        $identification = $list->getIdentification();
        $products = $this->productsFactory->createProductsFromWcOrder($order);
        $country = $this->getOrderCountry($order);
        $updateListCommand = $this->updateListCommand->withLongId($identification->getLongId())->withTransactionId($identification->getTransactionId())->withPayment($payment)->withProducts($products)->withCallback($callback)->withCountry($country)->withCustomer($customer)->withSystem($this->system);
        return $updateListCommand;
    }
    /**
     * Get a country from order.
     *
     * Try shipping country first, use billing country as a fallback.
     *
     * @param WC_Order $order
     *
     * @return string
     */
    protected function getOrderCountry(WC_Order $order) : string
    {
        return $order->get_shipping_country() ?: $order->get_billing_country();
    }
}
