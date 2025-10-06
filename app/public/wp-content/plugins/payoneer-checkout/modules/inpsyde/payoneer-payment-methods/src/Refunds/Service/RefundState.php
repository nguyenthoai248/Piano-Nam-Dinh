<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Service;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\RefundStatusDefinition;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Storage\AsyncRefundStatusStorageInterface;
use LogicException;
use WC_Order;
/**
 * @see RefundStateTest - unit tests for this class.
 */
class RefundState implements RefundStateInterface
{
    private AsyncRefundStatusStorageInterface $status;
    private ?WC_Order $order = null;
    public function __construct(AsyncRefundStatusStorageInterface $status)
    {
        $this->status = $status;
    }
    public function withWcOrder(WC_Order $order): self
    {
        $instance = clone $this;
        $instance->order = $order;
        return $instance;
    }
    public function isApiProcessingLocked(): bool
    {
        return $this->currentStatusIs(RefundStatusDefinition::STATUS_API_CALL);
    }
    public function isRefundPending(): bool
    {
        return $this->currentStatusIs(RefundStatusDefinition::STATUS_PENDING);
    }
    public function didRefundSucceed(): bool
    {
        return $this->currentStatusIs(RefundStatusDefinition::STATUS_SUCCESS);
    }
    public function didRefundFail(): bool
    {
        return $this->currentStatusIs(RefundStatusDefinition::STATUS_FAILED);
    }
    public function hasNoRefundStatus(): bool
    {
        return $this->currentStatusIs(RefundStatusDefinition::STATUS_NONE);
    }
    // Actions ---
    public function lockForApiProcessing(): bool
    {
        return $this->transitionStatusTo(RefundStatusDefinition::STATUS_API_CALL);
    }
    public function markAsAwaitingWebhook(): bool
    {
        return $this->transitionStatusTo(RefundStatusDefinition::STATUS_PENDING);
    }
    public function markInstantRefundComplete(): bool
    {
        return $this->transitionStatusTo(RefundStatusDefinition::STATUS_SUCCESS);
    }
    public function startAsyncRefund(): bool
    {
        return $this->transitionStatusTo(RefundStatusDefinition::STATUS_PENDING);
    }
    public function markAsSuccessful(): bool
    {
        return $this->transitionStatusTo(RefundStatusDefinition::STATUS_SUCCESS);
    }
    public function markAsFailed(): bool
    {
        return $this->transitionStatusTo(RefundStatusDefinition::STATUS_FAILED);
    }
    public function clearStatus(): bool
    {
        return $this->transitionStatusTo(RefundStatusDefinition::STATUS_NONE);
    }
    private function currentStatus(): string
    {
        $this->ensureOrderSet();
        assert($this->order instanceof WC_Order);
        // for static code analysis.
        return $this->status->currentRefundStatus($this->order) ?? '';
    }
    private function currentStatusIs(string $expected): bool
    {
        return $expected === $this->currentStatus();
    }
    private function transitionStatusTo(string $newStatus): bool
    {
        $this->ensureOrderSet();
        assert($this->order instanceof WC_Order);
        // for static code analysis.
        $currentStatus = $this->currentStatus();
        if (!RefundStatusDefinition::isValidTransition($currentStatus, $newStatus)) {
            do_action('payoneer-checkout.refund.invalid_status_transition', ['orderId' => $this->order->get_id(), 'fromStatus' => $currentStatus ?: 'empty', 'toStatus' => $newStatus ?: 'empty']);
            return \false;
        }
        $this->status->changeRefundStatusTo($this->order, $newStatus);
        do_action('payoneer-checkout.refund.status_changed', ['orderId' => $this->order->get_id(), 'fromStatus' => $currentStatus ?: 'empty', 'toStatus' => $newStatus ?: 'empty']);
        return \true;
    }
    private function ensureOrderSet(): void
    {
        if ($this->order) {
            return;
        }
        throw new LogicException('Order must be set using withWcOrder() before calling this method');
    }
}
