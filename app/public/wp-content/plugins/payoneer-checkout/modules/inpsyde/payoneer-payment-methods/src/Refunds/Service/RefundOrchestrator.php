<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Service;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\RefundContext;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Admin\RefundFailureEmailSender;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\RefundTextContents;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Storage\AsyncFailedRefundRegistryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Storage\AsyncRefundIntentStorageInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Storage\PayoutToRefundMappingInterface;
use RuntimeException;
use WC_Order;
use WC_Order_Refund;
/**
 * @see RefundOrchestratorTest - unit tests for this class.
 */
class RefundOrchestrator implements RefundOrchestratorInterface
{
    private array $payoneerPaymentGatewaysIds;
    private RefundTextContents $texts;
    private PayoutToRefundMappingInterface $payoutMapping;
    private RefundStateInterface $refundState;
    private AsyncRefundIntentStorageInterface $refundIntent;
    private AsyncFailedRefundRegistryInterface $failedRefunds;
    private RefundFailureEmailSender $failureEmailSender;
    public function __construct(array $payoneerPaymentGatewaysIds, RefundTextContents $texts, PayoutToRefundMappingInterface $payoutMapping, RefundStateInterface $refundState, AsyncRefundIntentStorageInterface $refundIntent, AsyncFailedRefundRegistryInterface $failedRefunds, RefundFailureEmailSender $failureEmailSender)
    {
        $this->payoneerPaymentGatewaysIds = $payoneerPaymentGatewaysIds;
        $this->texts = $texts;
        $this->payoutMapping = $payoutMapping;
        $this->refundState = $refundState;
        $this->refundIntent = $refundIntent;
        $this->failedRefunds = $failedRefunds;
        $this->failureEmailSender = $failureEmailSender;
    }
    public function preparePayoutRequest(WC_Order $wcOrder): RefundHandlerResult
    {
        if (!$this->isOrderPaidWithPayoneer($wcOrder)) {
            return RefundHandlerResult::notHandled('Not eligible: order was not paid with Payoneer');
        }
        $state = $this->refundStateFromOrder($wcOrder);
        // Check if already processing or pending
        if ($state->isApiProcessingLocked()) {
            return RefundHandlerResult::notHandled('Ignored: refund is currently being processed');
        }
        if ($state->isRefundPending()) {
            return RefundHandlerResult::notHandled('Ignored: a different refund is still processing');
        }
        // Lock the order for API processing
        if (!$state->lockForApiProcessing()) {
            return RefundHandlerResult::notHandled('Unable to lock order for processing');
        }
        return RefundHandlerResult::isEligible('Order locked and ready for API request');
    }
    public function handlePayoutResponse(RefundContext $context, WC_Order $wcOrder, ?WC_Order_Refund $wcRefund = null): RefundHandlerResult
    {
        $state = $this->refundStateFromOrder($wcOrder);
        // Verify order is in correct state for processing response
        if (!$state->isApiProcessingLocked()) {
            return RefundHandlerResult::notHandled('Ignored: order not in processing state');
        }
        // Handle API failure and clean up.
        if ($context->didFail()) {
            $state->clearStatus();
            $this->refundIntent->clearRefundIntent($wcOrder);
            if ($wcRefund) {
                $wcRefund->delete(\true);
            }
            return RefundHandlerResult::failedImmediately('Instant failure processed, data cleaned up');
        }
        if (!$wcRefund) {
            return RefundHandlerResult::awaitingRefundObject('Need WC_Order_Refund for processing');
        }
        // Handle async refund (API returned pending status)
        if ($context->isPending()) {
            return $this->startAsyncRefund($wcOrder, $wcRefund);
        }
        // Handle instant refund (API returned success status)
        if ($context->wasSuccessful()) {
            return $this->processInstantSuccess($context, $wcOrder, $wcRefund);
        }
        return RefundHandlerResult::notHandled('Ignored: unrecognized API response status');
    }
    public function handleWebhookNotification(RefundContext $context, WC_Order $wcOrder): RefundHandlerResult
    {
        if (!$this->isOrderPaidWithPayoneer($wcOrder)) {
            return RefundHandlerResult::notHandled('Not eligible: order was not paid with Payoneer');
        }
        $state = $this->refundStateFromOrder($wcOrder);
        $isPending = $state->isRefundPending();
        // Async refund waiting for confirmation.
        $hasNoState = $state->hasNoRefundStatus();
        // Payoneer-initiated refund sync.
        $flipToFail = $state->didRefundSucceed() && $context->didFail();
        // Edge case.
        if (!$isPending && !$hasNoState && !$flipToFail) {
            return RefundHandlerResult::notHandled('Notification ignored: not relevant');
        }
        $wcRefund = $this->refundIntent->refundIntent($wcOrder);
        if ($flipToFail || $context->didFail()) {
            return $this->handleWebhookRefundFailure($context, $wcOrder, $wcRefund);
        }
        if (!$context->wasSuccessful()) {
            return RefundHandlerResult::notHandled('Ignored: unrecognized notification status');
        }
        return $this->handleWebhookRefundSuccess($context, $wcOrder, $wcRefund);
    }
    public function clearFailedRefundState(WC_Order $wcOrder): void
    {
        $state = $this->refundState->withWcOrder($wcOrder);
        $state->clearStatus();
        $this->refundIntent->clearRefundIntent($wcOrder);
        $this->failedRefunds->removeFailedOrder($wcOrder->get_id());
    }
    // Helpers -----
    private function processInstantSuccess(RefundContext $context, WC_Order $wcOrder, WC_Order_Refund $wcRefund): RefundHandlerResult
    {
        $state = $this->refundStateFromOrder($wcOrder);
        if (!$this->isRefundPossible($wcRefund)) {
            return RefundHandlerResult::notHandled('Not possible: order is already refunded');
        }
        // Process instant refund and transition to success
        $this->finalizeSuccessfulRefund($context, $wcRefund);
        if (!$state->markInstantRefundComplete()) {
            return RefundHandlerResult::webhookFailure('Failed to mark instant refund as complete');
        }
        return RefundHandlerResult::processedImmediately('Instant refund processed successfully');
    }
    private function startAsyncRefund(WC_Order $wcOrder, WC_Order_Refund $wcRefund): RefundHandlerResult
    {
        $state = $this->refundStateFromOrder($wcOrder);
        // Store refund intent and transition to pending
        // $this->refundIntent->storeRefundIntent($wcOrder, $wcRefund);
        if (!$state->markAsAwaitingWebhook()) {
            return RefundHandlerResult::webhookFailure('Failed to transition to pending state');
        }
        $wcOrder->add_order_note($this->texts->orderNoticeStatusPending());
        /**
         * This method usually runs in the action `woocommerce_create_refund` which fires _before_
         * the WC_Order_Refund is saved to the database. In this case, the ID is 0, and we don't
         * need to take any action.
         *
         * However, calling `wc_refund_payment()` directly also invokes this method, and in this
         * case, the WC_Order_Refund is very likely already saved to the database, and we must
         * delete it again to avoid problems later: For async refunds, the object must be created
         * in the database only by the `handleWebhookRefundSuccess()` method below.
         */
        if ($wcRefund->get_id() > 0) {
            $wcRefund->delete(\true);
        }
        return RefundHandlerResult::awaitingWebhook('Async refund initiated, awaiting webhook confirmation');
    }
    private function handleWebhookRefundSuccess(RefundContext $context, WC_Order $wcOrder, ?WC_Order_Refund $wcRefund): RefundHandlerResult
    {
        /**
         * Attempt to create a new WC_Order_Refund entry, which updatest the status of the
         * parent order on success.
         */
        $wcRefund = $this->prepareRefundFromOrder($context, $wcOrder, $wcRefund);
        if (!$wcRefund) {
            return RefundHandlerResult::notHandled('Notification ignored: no refund data found');
        }
        if (!$this->isRefundPossible($wcRefund)) {
            return RefundHandlerResult::notHandled('Notification ignored: order is already refunded');
        }
        $state = $this->refundStateFromOrder($wcOrder);
        if (!$state->markAsSuccessful()) {
            return RefundHandlerResult::webhookFailure('Could not mark refund as successful - invalid state transition');
        }
        $this->finalizeSuccessfulRefund($context, $wcRefund);
        $this->refundIntent->clearRefundIntent($wcOrder);
        $wcOrder->add_order_note($this->texts->orderNoticeStatusSuccess());
        return RefundHandlerResult::webhookSuccess();
    }
    private function handleWebhookRefundFailure(RefundContext $context, WC_Order $wcOrder, ?WC_Order_Refund $wcRefund): RefundHandlerResult
    {
        $state = $this->refundStateFromOrder($wcOrder);
        if (!$state->markAsFailed()) {
            return RefundHandlerResult::webhookFailure('Could not mark refund as failed - invalid state transition');
        }
        $this->refundIntent->clearRefundIntent($wcOrder);
        if ($wcRefund) {
            $wcRefund->delete(\true);
        }
        $wcOrder->add_order_note($this->texts->orderNoticeStatusFailed());
        $this->failedRefunds->addFailedOrder($wcOrder->get_id());
        $this->failureEmailSender->sendFailureForOrder($wcOrder->get_id());
        return RefundHandlerResult::webhookFailure('Async refund failed: ' . $context->reasonCode());
    }
    private function finalizeSuccessfulRefund(RefundContext $context, WC_Order_Refund $wcRefund): void
    {
        $payoutLongId = $context->longId();
        if (!$payoutLongId) {
            return;
        }
        $this->updateRefundReason($wcRefund, $payoutLongId);
        $this->payoutMapping->storePayoutId($wcRefund, $payoutLongId);
        $wcRefund->save();
    }
    private function updateRefundReason(WC_Order_Refund $wcRefund, string $payoutLongId): void
    {
        $fullReason = $this->texts->refundDescriptionWithPayoutId($payoutLongId, $wcRefund->get_reason());
        $wcRefund->set_reason($fullReason);
    }
    // Eligibility checks -----
    private function isOrderPaidWithPayoneer(WC_Order $wcOrder): bool
    {
        return in_array($wcOrder->get_payment_method(), $this->payoneerPaymentGatewaysIds, \true);
    }
    private function isRefundPossible(WC_Order_Refund $wcRefund): bool
    {
        return !$this->payoutMapping->hasPayoutId($wcRefund);
    }
    // Factory and transformers -----
    private function orderFromRefund(WC_Order_Refund $wcRefund): WC_Order
    {
        $wcOrderId = $wcRefund->get_parent_id();
        $wcOrder = wc_get_order($wcOrderId);
        if (!$wcOrder instanceof WC_Order) {
            throw new RuntimeException('Refund is not linked to an order');
        }
        return $wcOrder;
    }
    private function refundStateFromOrder(WC_Order $wcOrder): RefundStateInterface
    {
        return $this->refundState->withWcOrder($wcOrder);
    }
    // Create Refund object for webhook -----
    private function prepareRefundFromOrder(RefundContext $context, WC_Order $wcOrder, ?WC_Order_Refund $intendedRefund): ?WC_Order_Refund
    {
        // When a stored refund intent is found, then use it directly.
        if ($intendedRefund && $intendedRefund->get_id() > 0) {
            return $intendedRefund;
        }
        // Create a refund object when no refund intent is locally available.
        $args = $this->prepareRefundArgs($context, $wcOrder, $intendedRefund);
        $actualRefund = wc_create_refund($args);
        if ($actualRefund instanceof WC_Order_Refund) {
            return $actualRefund;
        }
        return null;
    }
    private function prepareRefundArgs(RefundContext $context, WC_Order $wcOrder, ?WC_Order_Refund $wcRefund): array
    {
        return ['order_id' => $wcOrder->get_id(), 'amount' => $this->prepareRefundAmount($context->amount(), $wcRefund), 'reason' => $this->prepareRefundReason($context->longId(), $wcRefund), 'line_items' => $this->prepareRefundLineItems($wcOrder, $wcRefund), 'refund_payment' => \false];
    }
    private function prepareRefundAmount(float $webhookAmount, ?WC_Order_Refund $wcRefund): float
    {
        if ($wcRefund) {
            $intentionAmount = (float) $wcRefund->get_amount();
            if (abs($intentionAmount - $webhookAmount) > 0.01) {
                do_action('payoneer-checkout.refund-handler.amount_mismatch', ['refundId' => $wcRefund->get_id(), 'orderId' => $wcRefund->get_parent_id(), 'intentionAmount' => $intentionAmount, 'webhookAmount' => $webhookAmount]);
            }
        }
        // We always use the webhook amount.
        return $webhookAmount;
    }
    private function prepareRefundReason(string $notificationId, ?WC_Order_Refund $wcRefund): string
    {
        if ($wcRefund && $wcRefund->get_reason()) {
            return $wcRefund->get_reason();
        }
        return $this->texts->refundDescriptionFromNotification($notificationId);
    }
    private function prepareRefundLineItems(WC_Order $order, ?WC_Order_Refund $wcRefund): array
    {
        if ($wcRefund) {
            return $wcRefund->get_items();
        }
        return $order->get_items(['line_item', 'fee', 'shipping']);
    }
}
