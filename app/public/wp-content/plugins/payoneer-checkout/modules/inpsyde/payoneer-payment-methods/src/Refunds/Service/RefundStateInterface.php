<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Service;

use WC_Order;
/**
 * Manages async refund status lifecycle on orders.
 *
 * Tracks the progression of async refunds through pending, success, and failure states.
 * Ensures proper state transitions and integrates with failure tracking for merchant notifications.
 */
interface RefundStateInterface
{
    /**
     * Returns a new object using the provided WC_Order as context.
     */
    public function withWcOrder(WC_Order $order): self;
    /**
     * Returns true when order is currently locked for API processing.
     */
    public function isApiProcessingLocked(): bool;
    /**
     * Returns true when the refund is in pending async status.
     *
     * The refund was accepted by the Payoneer API, but we have not yet
     * received a success or failure decision via webhook. This is a temporary
     * state that should eventually transition to failed or successful.
     */
    public function isRefundPending(): bool;
    /**
     * Returns true when the refund completed successfully.
     *
     * The refund was accepted and the payout transaction was confirmed
     * by the payment provider API.
     */
    public function didRefundSucceed(): bool;
    /**
     * Returns true when the refund attempt failed.
     *
     * The API rejected the payout action and the refund was not processed.
     */
    public function didRefundFail(): bool;
    /**
     * Returns true when no refund status exists.
     *
     * Either no refund attempt was made yet, or a previous failure status
     * was dismissed by the merchant.
     */
    public function hasNoRefundStatus(): bool;
    // Actions ---
    /**
     * Locks the order before making API request to prevent concurrent processing.
     */
    public function lockForApiProcessing(): bool;
    /**
     * Transitions from API_CALL to PENDING for async refunds.
     */
    public function markAsAwaitingWebhook(): bool;
    /**
     * Transitions from API_CALL to SUCCESS for instant refunds.
     */
    public function markInstantRefundComplete(): bool;
    /**
     * Initiates an asynchronous refund process.
     *
     * Transitions the state to pending and stores refund intention data
     * for later processing when the webhook confirms completion.
     */
    public function startAsyncRefund(): bool;
    /**
     * Marks the refund as successfully completed.
     *
     * Typically called when a webhook confirms the refund was processed
     * by the payment provider.
     */
    public function markAsSuccessful(): bool;
    /**
     * Marks the refund as failed.
     *
     * Records the failure and adds the order to the failed refund registry
     * for merchant review.
     */
    public function markAsFailed(): bool;
    /**
     * Clears the refund status back to no-status state.
     *
     * Removes the order from the failed refund registry and resets
     * the refund state to allow new refund attempts.
     */
    public function clearStatus(): bool;
}
