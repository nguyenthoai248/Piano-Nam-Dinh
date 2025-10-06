<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment;

/**
 * A structure containing payment data.
 */
interface PaymentInterface
{
    /**
     * Return payment reference.
     *
     * Return a short description of order given by merchant.
     *
     * @return string Short payment description.
     */
    public function getReference(): string;
    /**
     * Return payment amount.
     *
     * @return float Amount of payment.
     */
    public function getAmount(): float;
    /**
     * Return tax amount of payment.
     *
     * @return float Amount of tax of the payment.
     */
    public function getTaxAmount(): float;
    /**
     * Return net amount of payment.
     *
     * @return float
     */
    public function getNetAmount(): float;
    /**
     * Return currency code.
     *
     * Return value format according to ISO-4217 form, e.g. "EUR", "USD".
     *
     * @return string Currency code.
     */
    public function getCurrency(): string;
    /**
     * Return the ID of invoice for the Payment.
     *
     * @return string Invoice id.
     */
    public function getInvoiceId(): string;
}
