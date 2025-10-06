<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\Controller;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\OrderPaymentWebhookHandler\OrderPaymentWebhookHandlerInterface;
use WC_Order;
use WP_REST_Request;
class OrderPaymentWebhookStrategyHandler
{
    /**
     * @var array<OrderPaymentWebhookHandlerInterface>
     */
    protected $handlers = [];
    public function __construct(OrderPaymentWebhookHandlerInterface ...$handlers)
    {
        array_push($this->handlers, ...$handlers);
    }
    /**
     * Selects the correct strategies and executes them.
     *
     * @param WP_REST_Request $request
     * @param WC_Order $order
     */
    public function handleStrategies(WP_REST_Request $request, WC_Order $order): void
    {
        $acceptedHandlers = $this->selectStrategies($request, $order);
        foreach ($acceptedHandlers as $handler) {
            /**
             * @param OrderPaymentWebhookHandlerInterface $handler
             */
            $handler->handlePayment($request, $order);
        }
    }
    /**
     * @param WP_REST_Request $request
     * @param WC_Order $order
     *
     * @return array<OrderPaymentWebhookHandlerInterface>
     */
    public function selectStrategies(WP_REST_Request $request, WC_Order $order): array
    {
        $acceptedHandlers = [];
        foreach ($this->handlers as $handler) {
            /**
             * @param OrderPaymentWebhookHandlerInterface $handler
             */
            if ($handler->accepts($request, $order)) {
                $acceptedHandlers[] = $handler;
            }
        }
        return $acceptedHandlers;
    }
}
