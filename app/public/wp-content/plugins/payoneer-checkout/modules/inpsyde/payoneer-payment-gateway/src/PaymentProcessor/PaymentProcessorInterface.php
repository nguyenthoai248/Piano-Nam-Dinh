<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\PaymentProcessor;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Exception\PaymentGatewayExceptionInterface;
use WC_Order;
/**
 * Interface for processing payments/transactions for a new WC_Order.
 *
 * @see \WC_Payment_Gateway::process_payment()
 * @psalm-type PaymentResultInfo = 'success'|'failure'
 * @psalm-type PaymentResult = array{
 *     result: PaymentResultInfo,
 *     redirect?: string
 * }
 */
interface PaymentProcessorInterface
{
    /**
     * Carry out payment transactions via API call or other means
     * Update order status accordingly and save required meta data
     * Return array of instructions telling WooCommerce how to interpret the result
     *
     * @param WC_Order $order The unpaid WC_Order
     *
     * @return array
     * @psalm-return PaymentResult
     * @throws PaymentGatewayExceptionInterface If the processor fails to process the transaction
     */
    public function processPayment(WC_Order $order) : array;
}
