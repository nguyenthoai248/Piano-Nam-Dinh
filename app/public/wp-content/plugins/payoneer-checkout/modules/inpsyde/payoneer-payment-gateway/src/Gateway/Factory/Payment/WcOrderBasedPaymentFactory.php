<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Payment;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentInterface;
use WC_Order;
class WcOrderBasedPaymentFactory implements WcOrderBasedPaymentFactoryInterface
{
    /**
     * @var PaymentFactoryInterface
     */
    protected $paymentFactory;
    /**
     * @var string
     */
    protected $storeIdentifier;
    /**
     * @param PaymentFactoryInterface $paymentFactory
     * @param string $storeIdentifier Any string identifying store: URL, Site title, etc.
     */
    public function __construct(PaymentFactoryInterface $paymentFactory, string $storeIdentifier)
    {
        $this->paymentFactory = $paymentFactory;
        // Reference is appended later to the returnUrl,
        // so we need to sanitize it as strict as possible.
        $this->storeIdentifier = sanitize_key($storeIdentifier);
    }
    /**
     * @inheritDoc
     */
    public function createPayment(WC_Order $order) : PaymentInterface
    {
        $reference = $this->prepareOrderReference($order);
        /**
         * WooCommerce docblock says the return value of WC_Order::get_total() and
         * WC_Order::get_total_tax() are floats, but actually it's a string.
         *
         * @psalm-suppress RedundantCastGivenDocblockType
         */
        $totalAmount = (float) $order->get_total();
        /** @psalm-suppress RedundantCastGivenDocblockType */
        $taxAmount = (float) $order->get_total_tax();
        $taxAmount = wc_round_tax_total($taxAmount);
        $netAmount = $totalAmount - $taxAmount;
        $payment = $this->paymentFactory->createPayment($reference, $totalAmount, $taxAmount, $netAmount, $order->get_currency(), $order->get_order_number());
        return $payment;
    }
    /**
     * Prepare a short order description.
     *
     * @param WC_Order $order
     *
     * @return string
     */
    protected function prepareOrderReference(WC_Order $order) : string
    {
        return sprintf('Order #%1$s from %2$s.', $order->get_order_number(), $this->storeIdentifier);
    }
}
