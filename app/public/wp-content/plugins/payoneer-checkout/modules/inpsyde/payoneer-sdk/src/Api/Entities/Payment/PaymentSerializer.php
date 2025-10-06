<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment;

class PaymentSerializer implements PaymentSerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serializePayment(PaymentInterface $payment): array
    {
        return ['reference' => $payment->getReference(), 'amount' => $payment->getAmount(), 'taxAmount' => $payment->getTaxAmount(), 'netAmount' => $payment->getNetAmount(), 'currency' => $payment->getCurrency(), 'invoiceId' => $payment->getInvoiceId()];
    }
}
