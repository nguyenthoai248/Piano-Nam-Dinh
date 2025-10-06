<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds;

/**
 * Centralizes all (translatable) text content for the refund module.
 */
class RefundTextContents
{
    /**
     * Admin facing refund reason: Refund was created by API request.
     */
    public function refundDescriptionWithPayoutId(string $payoutId, string $reason = ''): string
    {
        return $this->getRefundDescription('api', $payoutId, $reason);
    }
    /**
     * Admin facing refund reason: Refund was created via a webhook notification.
     */
    public function refundDescriptionFromNotification(string $notificationId): string
    {
        return $this->getRefundDescription('notification', $notificationId, '');
    }
    /**
     * Text of the order notice that's created when a deferred refund is initiated.
     */
    public function orderNoticeStatusPending(): string
    {
        /* translators: Content of the ORDER NOTE when an async refund started */
        return __('A refund request was started and is processing in the background.', 'payoneer-checkout');
    }
    /**
     * Text of the order notice that's created when a deferred refund fails (via webhook).
     *
     * @todo: We want to include failure details, but lack of documentation prevents us from this.
     */
    public function orderNoticeStatusFailed(): string
    {
        /* translators: Content of the ORDER NOTE when an async refund failed */
        return __('The refund for this order failed.', 'payoneer-checkout');
    }
    /**
     * Text of the order notice that's created when a deferred refund succeeds.
     */
    public function orderNoticeStatusSuccess(): string
    {
        /* translators: Content of the ORDER NOTE when an async refund succeeded */
        return __('Refund completed successfully.', 'payoneer-checkout');
    }
    // --- Order Details Page ---
    /**
     * Label that's displayed instead of the "Refund" button while a deferred refund is pending.
     */
    public function orderRefundProcessingButtonText(): string
    {
        /* translators: Label of the disabled REFUND BUTTON while async refund is in progress */
        return __('Refund processing...', 'payoneer-checkout');
    }
    /**
     * Inline notice on the order details page while a deferred refund is pending.
     */
    public function orderRefundPendingNotice(): string
    {
        /* translators: Notice displayed on the ORDER DETAILS page while an async refund is pending */
        return __('The refund for this order is still processing. You cannot modify the order status until the refund is completed.', 'payoneer-checkout');
    }
    /**
     * Inline notice on order details page when a refund failed via a webhook notification.
     *
     * @todo: We want to include failure details, but lack of documentation prevents us from this.
     */
    public function orderRefundFailedNotice(): string
    {
        /* translators: Notice displayed on the ORDER DETAILS page once an async refund failed */
        return __('The refund for this order failed.', 'payoneer-checkout');
    }
    // --- Global Notifications ---
    /**
     * Notification that's displayed on all wp-admin pages _except_ on the order details page.
     */
    public function globalRefundFailedNotice(int $orderId): string
    {
        return sprintf(
            /* translators: site-wide notice for failed async refund.
               1: URL pointing to the order details page. 2: WooCommerce order ID */
            __('The refund for <a href="%1$s">order #%2$s</a> failed.', 'payoneer-checkout'),
            $this->editOrderUrl($orderId),
            $orderId
        );
    }
    // --- Email Notifications ---
    /**
     * Email subject; must be plain-text.
     */
    public function failureEmailSubject(int $orderId): string
    {
        return sprintf(
            /* translators: Subject of the async-refund-failed EMAIL sent to shop admin.
               1: The WooCommerce Order ID */
            __('Refund failed for order #%1$s', 'payoneer-checkout'),
            $orderId
        );
    }
    /**
     * Full content of the refund-failure email; can include HTML and CSS.
     *
     * @todo: We want to include failure details, but lack of documentation prevents us from this.
     */
    public function failureEmailMessage(int $orderId): string
    {
        return sprintf(
            /* translators: Body of the async-refund-failed EMAIL sent to the shop admin.
               1: URL pointing to the order details page. 2: WooCommerce order ID */
            __('<p>Hello,</p><p>Please note that the refund for order #%2$s failed.<br>Please check the <a href="%1$s">order details</a>.</p><p>Thank you</p>', 'payoneer-checkout'),
            $this->editOrderUrl($orderId),
            $orderId
        );
    }
    // --- Helper Methods ---
    private function editOrderUrl(int $orderId): string
    {
        return \Automattic\WooCommerce\Utilities\OrderUtil::get_order_admin_edit_url($orderId);
    }
    private function getRefundDescription(string $source, string $transactionId, string $reason): string
    {
        $origins = ['api' => __('Refunded by Payoneer Checkout.', 'payoneer-checkout'), 'notification' => __('Refunded automatically on incoming webhook.', 'payoneer-checkout')];
        if (!array_key_exists($source, $origins)) {
            return $reason;
        }
        foreach ($origins as $originTemplate) {
            if (strpos($reason, $originTemplate) !== \false) {
                return $reason;
            }
        }
        $parts = [];
        if ($reason) {
            $parts[] = $reason;
        }
        $parts[] = $origins[$source];
        if ($transactionId) {
            $parts[] = sprintf(
                /* translators: Suffix added to REFUND REASON in all cases.
                   %1$s is replaced with the transaction-long-ID. */
                __('Transaction ID: %1$s', 'payoneer-checkout'),
                $transactionId
            );
        }
        return implode(' ', $parts);
    }
}
