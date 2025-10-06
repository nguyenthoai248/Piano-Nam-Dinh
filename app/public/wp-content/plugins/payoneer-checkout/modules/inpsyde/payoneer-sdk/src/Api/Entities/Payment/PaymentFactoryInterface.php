<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
/**
 * Service able to create Payment object.
 */
interface PaymentFactoryInterface
{
    /**
     * Create a new Payment instance.
     *
     * @param string $reference Short description by merchant.
     * @param float $amount Payment amount.
     * @param float $taxAmount Amount of payment tax.
     * @param float $netAmount Net amount of payment.
     * @param string $currency Payment currency code.
     * @param string|null $invoiceId Payment invoice ID.
     *
     * @return PaymentInterface Created payment object.
     *
     * @throws ApiExceptionInterface If failed to create payment object.
     */
    public function createPayment(string $reference, float $amount, float $taxAmount, float $netAmount, string $currency, ?string $invoiceId = null): PaymentInterface;
}
