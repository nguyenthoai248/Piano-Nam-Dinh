<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\RefundProcessor;

use Exception;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use WC_Order;
/**
 * Service able to process refund (Payout in Payoneer terms).
 */
interface RefundProcessorInterface
{
    /**
     * @return ListInterface Payout representation.
     *
     * @throws Exception If failed to refund payment.
     */
    public function refundOrderPayment(WC_Order $order, float $amount, string $reason) : ListInterface;
}
