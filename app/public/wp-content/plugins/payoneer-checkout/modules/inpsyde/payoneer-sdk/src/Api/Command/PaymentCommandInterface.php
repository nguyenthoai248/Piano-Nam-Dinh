<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
/**
 * Represents a payment-related command.
 */
interface PaymentCommandInterface extends CommandInterface
{
    /**
     * @param string $transactionId
     *
     * @return static
     */
    public function withTransactionId(string $transactionId): self;
    /**
     * Return new instance with provided payment.
     *
     * @param PaymentInterface $payment A payment to add to a new instance.
     *
     * @return static Created new instance.
     */
    public function withPayment(PaymentInterface $payment): self;
    /**
     * Return a new instance with provided products.
     *
     * @param ProductInterface[] $products
     *
     * @return static Created new instance.
     */
    public function withProducts(array $products): self;
    /**
     * Return currently configured payment.
     *
     * @return PaymentInterface|null
     */
    public function getPayment(): ?PaymentInterface;
    /**
     * Return currently configured products.
     *
     * @return ProductInterface[]
     */
    public function getProducts(): array;
}
