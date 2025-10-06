<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment;

class PaymentFactory implements PaymentFactoryInterface
{
    /**
     * @var InvoiceIdProviderInterface
     */
    private $invoiceIdProvider;
    public function __construct(InvoiceIdProviderInterface $invoiceIdProvider)
    {
        $this->invoiceIdProvider = $invoiceIdProvider;
    }
    /**
     * @inheritDoc
     */
    public function createPayment(string $reference, float $amount, float $taxAmount, float $netAmount, string $currency, ?string $invoiceId = null): PaymentInterface
    {
        $invoiceId = $invoiceId ?? $this->invoiceIdProvider->provide();
        return new Payment($reference, $amount, $taxAmount, $netAmount, $currency, $invoiceId);
    }
}
