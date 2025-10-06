<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
/**
 * Service able to create Payment instance from array.
 */
interface PaymentDeserializerInterface
{
    /**
     * @param array {
     *     reference: string,
     *     amount: float,
     *     taxAmount: float,
     *     netAmount: float,
     *     currency: string,
     *     invoiceId: string
     * } $paymentData Payment amount, currency and description.
     *
     * @return PaymentInterface Created payment object.
     *
     * @throws ApiExceptionInterface If something went wrong.
     */
    public function deserializePayment(array $paymentData): PaymentInterface;
}
