<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment;

class Payment implements PaymentInterface
{
    /**
     * @var string Transaction description given by merchant.
     */
    protected $reference;
    /**
     * @var float Payment amount.
     */
    protected $amount;
    /**
     * @var string Payment currency code.
     */
    protected $currency;
    /**
     * @var string
     */
    protected $invoiceId;
    /**
     * @var float
     */
    protected $taxAmount;
    /**
     * @var float
     */
    private $netAmount;
    /**
     * @param string $reference Short description of the payment.
     * @param float $amount Payment amount.
     * @param float $taxAmount Payment tax amount.
     * @param float $netAmount Payment net amount.
     * @param string $currency Payment currency code.
     */
    public function __construct(string $reference, float $amount, float $taxAmount, float $netAmount, string $currency, string $invoiceId)
    {
        $this->reference = $reference;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->invoiceId = $invoiceId;
        $this->taxAmount = $taxAmount;
        $this->netAmount = $netAmount;
    }
    /**
     * @inheritDoc
     */
    public function getReference(): string
    {
        return $this->reference;
    }
    /**
     * @inheritDoc
     */
    public function getAmount(): float
    {
        return $this->amount;
    }
    /**
     * @inheritDoc
     */
    public function getTaxAmount(): float
    {
        return $this->taxAmount;
    }
    /**
     * @inheritDoc
     */
    public function getNetAmount(): float
    {
        return $this->netAmount;
    }
    /**
     * @inheritDoc
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }
    /**
     * @inheritDoc
     */
    public function getInvoiceId(): string
    {
        return $this->invoiceId;
    }
}
