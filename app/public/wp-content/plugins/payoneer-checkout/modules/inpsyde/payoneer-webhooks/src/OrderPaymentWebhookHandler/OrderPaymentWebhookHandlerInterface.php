<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\OrderPaymentWebhookHandler;

use WC_Order;
use WP_REST_Request;
interface OrderPaymentWebhookHandlerInterface
{
    /**
     * Declares itself fit for handling the payment
     *
     * @param WP_REST_Request $request
     * @param WC_Order $order
     *
     * @return bool
     */
    public function accepts(WP_REST_Request $request, WC_Order $order): bool;
    /**
     * Handles the payment after a webhook.
     *
     * @param WP_REST_Request $request
     * @param WC_Order $order
     *
     * @return void
     */
    public function handlePayment(WP_REST_Request $request, WC_Order $order): void;
}
