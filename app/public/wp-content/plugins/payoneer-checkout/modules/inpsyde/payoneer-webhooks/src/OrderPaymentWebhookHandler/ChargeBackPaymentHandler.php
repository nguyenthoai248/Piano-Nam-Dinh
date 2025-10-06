<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\OrderPaymentWebhookHandler;

use WC_Order;
use WP_REST_Request;
class ChargeBackPaymentHandler implements OrderPaymentWebhookHandlerInterface
{
    /**
     * @inheritDoc
     */
    public function accepts(WP_REST_Request $request, WC_Order $order): bool
    {
        return (string) $request->get_param('statusCode') === 'charged_back';
    }
    /**
     * Handle a notification about payment is charged back.
     *
     * @param WP_REST_Request $request Incoming request.
     * @param WC_Order $order The order payment is charged back for.
     */
    public function handlePayment(WP_REST_Request $request, WC_Order $order): void
    {
        $notificationId = (string) $request->get_param('notificationId');
        $order->add_order_note(sprintf('Order marked as charged back on incoming webhook. Notification ID is %1$s', $notificationId));
        $order->save();
    }
}
