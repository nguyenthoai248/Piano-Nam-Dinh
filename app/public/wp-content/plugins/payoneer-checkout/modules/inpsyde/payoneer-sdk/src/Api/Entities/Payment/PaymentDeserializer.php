<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiException;
class PaymentDeserializer implements PaymentDeserializerInterface
{
    /**
     * @var PaymentFactoryInterface Service able to create a new Payment instance.
     */
    protected $paymentFactory;
    /**
     * @param PaymentFactoryInterface $paymentFactory To create a payment instance.
     */
    public function __construct(PaymentFactoryInterface $paymentFactory)
    {
        $this->paymentFactory = $paymentFactory;
    }
    /**
     * @inheritDoc
     */
    public function deserializePayment(array $paymentData): PaymentInterface
    {
        if (!isset($paymentData['reference'])) {
            throw new ApiException('Data contains no expected reference element.');
        }
        $reference = $paymentData['reference'];
        if (!isset($paymentData['amount'])) {
            throw new ApiException('Data contains no expected amount element.');
        }
        $amount = $paymentData['amount'];
        if (!isset($paymentData['taxAmount'])) {
            throw new ApiException('Data contains no expected taxAmount element.');
        }
        $taxAmount = $paymentData['taxAmount'];
        if (!isset($paymentData['netAmount'])) {
            throw new ApiException('Data contains no expected netAmount element.');
        }
        $netAmount = $paymentData['netAmount'];
        if (!isset($paymentData['currency'])) {
            throw new ApiException('Data contains no expected currency element.');
        }
        $currency = $paymentData['currency'];
        if (!isset($paymentData['invoiceId'])) {
            throw new ApiException('Data contains no expected invoiceId element');
        }
        $invoiceId = $paymentData['invoiceId'];
        return $this->paymentFactory->createPayment($reference, $amount, $taxAmount, $netAmount, $currency, $invoiceId);
    }
}
