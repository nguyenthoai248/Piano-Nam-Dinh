<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Service;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\RefundContext;
use WC_Order;
use WC_Order_Refund;
/**
 * Orchestrates refund workflows for both instant and async processing.
 */
interface RefundOrchestratorInterface
{
    /**
     * Locks order and validates eligibility before API call.
     * Prevents concurrent refund processing on the same order.
     */
    public function preparePayoutRequest(WC_Order $wcOrder): RefundHandlerResult;
    /**
     * Processes API response and transitions to appropriate next state.
     * Creates WC_Order_Refund for instant refunds, stores intent for async.
     */
    public function handlePayoutResponse(RefundContext $context, WC_Order $wcOrder, ?WC_Order_Refund $wcRefund = null): RefundHandlerResult;
    /**
     * Processes webhook notifications for async refunds.
     * Handles both success and failure scenarios based on context status.
     */
    public function handleWebhookNotification(RefundContext $context, WC_Order $wcOrder): RefundHandlerResult;
    /**
     * Resets failed refund state to dismiss admin notifications.
     */
    public function clearFailedRefundState(WC_Order $wcOrder): void;
}
