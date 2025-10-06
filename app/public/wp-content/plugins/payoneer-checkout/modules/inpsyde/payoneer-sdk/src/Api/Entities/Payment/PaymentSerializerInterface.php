<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment;

/**
 * Service able to convert PaymentInterface instance to array.
 * @psalm-type PaymentType = array {
 *                              reference: string,
 *                              amount: float,
 *                              taxAmount: float,
 *                              netAmount: float,
 *                              currency: string,
 *                              invoiceId: string
 *                           }
 */
interface PaymentSerializerInterface
{
    /**
     * @param PaymentInterface $payment Payment instance containing data.
     * @psalm-return PaymentType
     * @return array Resulting array.
     */
    public function serializePayment(PaymentInterface $payment): array;
}
