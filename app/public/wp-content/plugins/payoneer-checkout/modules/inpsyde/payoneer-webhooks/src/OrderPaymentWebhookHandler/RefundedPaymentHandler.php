<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\OrderPaymentWebhookHandler;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\RefundContext;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Service\RefundOrchestratorInterface;
use WC_Order;
use WP_REST_Request;
class RefundedPaymentHandler implements OrderPaymentWebhookHandlerInterface
{
    /**
     * An order field name where CHARGE ID should be saved.
     */
    private RefundOrchestratorInterface $refundOrchestrator;
    public function __construct(RefundOrchestratorInterface $refundOrchestrator)
    {
        $this->refundOrchestrator = $refundOrchestrator;
    }
    /**
     * @inheritDoc
     */
    public function accepts(WP_REST_Request $request, WC_Order $order): bool
    {
        $refundContext = RefundContext::fromRestRequest($request);
        if (!$refundContext->wasParsed()) {
            return \false;
        }
        if ($refundContext->isPending()) {
            return \false;
        }
        return $refundContext->hasRefundReason();
    }
    /**
     * Handle a notification about payment was refunded.
     */
    public function handlePayment(WP_REST_Request $request, WC_Order $order): void
    {
        $refundContext = RefundContext::fromRestRequest($request);
        $result = $this->refundOrchestrator->handleWebhookNotification($refundContext, $order);
        do_action('payoneer-checkout.refund-handler.webhook_result', ['message' => $result->statusMessage(), 'handled' => $result->handled(), 'success' => $result->successful()]);
    }
}
