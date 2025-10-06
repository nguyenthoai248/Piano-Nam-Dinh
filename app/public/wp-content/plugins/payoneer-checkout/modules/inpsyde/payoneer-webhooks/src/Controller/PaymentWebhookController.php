<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\Controller;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\OrderFinder\OrderFinderInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\OrderWebhookFinder\OrderWebhookFinderInterface;
use WC_Order;
use WP_REST_Request;
use WP_REST_Response;
/**
 * A service handling incoming webhook about payment.
 */
class PaymentWebhookController implements WpRestApiControllerInterface
{
    /**
     * @var OrderFinderInterface
     */
    protected $orderFinder;
    /**
     * @var string
     */
    protected $securityHeaderFieldName;
    /**
     * @var OrderWebhookFinderInterface
     */
    protected $orderWebhookFinder;
    /**
     * @var string
     */
    protected $webhooksReceivedFieldName;
    /** @var OrderPaymentWebhookStrategyHandler */
    protected $orderPaymentWebhookStrategyHandler;
    /**
     *
     * @param string $securityHeaderFieldName
     * @param OrderFinderInterface $orderFinder To find an order by transaction_id from webhook.
     * @param OrderWebhookFinderInterface $orderWebhookFinder
     * @param string $webhooksReceivedFieldName
     * @param OrderPaymentWebhookStrategyHandler $orderPaymentWebhookStrategyHandler
     */
    public function __construct(string $securityHeaderFieldName, OrderFinderInterface $orderFinder, OrderWebhookFinderInterface $orderWebhookFinder, string $webhooksReceivedFieldName, OrderPaymentWebhookStrategyHandler $orderPaymentWebhookStrategyHandler)
    {
        $this->orderFinder = $orderFinder;
        $this->securityHeaderFieldName = $securityHeaderFieldName;
        $this->orderWebhookFinder = $orderWebhookFinder;
        $this->webhooksReceivedFieldName = $webhooksReceivedFieldName;
        $this->orderPaymentWebhookStrategyHandler = $orderPaymentWebhookStrategyHandler;
    }
    /**
     * @inheritDoc
     */
    public function handleWpRestRequest(WP_REST_Request $request): WP_REST_Response
    {
        $transactionId = (string) $request->get_param('transactionId');
        $orders = $this->orderFinder->findOrdersByTransactionId($transactionId, 20);
        if (count($orders) > 1) {
            do_action('payoneer-checkout.webhook_request.multiple_orders_found_for_transaction_id', ['transactionId' => $transactionId, 'orders' => array_map(static fn(WC_Order $order) => $order->get_id(), $orders)]);
        }
        $order = $orders[0];
        $longId = (string) $request->get_param('longId');
        if (!$order instanceof WC_Order) {
            do_action('payoneer-checkout.webhook_request.order_not_found', ['transactionId' => $transactionId, 'longId' => $longId]);
            return new WP_REST_Response(null, 200);
        }
        if (!$this->authHeaderIsCorrect($order, $request)) {
            do_action('payoneer-checkout.webhook_request.order_auth_header_is_incorrect', ['orderId' => $order->get_id(), 'longId' => $longId]);
            return new WP_REST_Response(null, 200);
        }
        if ($this->isWebhookProcessed($request, $order)) {
            do_action('payoneer-checkout.webhook_request.webhook_already_processed', ['orderId' => $order->get_id(), 'longId' => $longId]);
            return new WP_REST_Response(null, 200);
        }
        $this->orderPaymentWebhookStrategyHandler->handleStrategies($request, $order);
        $this->saveOrderWebhookProcessedMeta($request, $order);
        return new WP_REST_Response(null, 200);
    }
    /**
     * Should we process this webhook or was already processed.
     *
     * @param WP_REST_Request $request Request to get data from.
     * @param WC_Order $order The order with the meta associated.
     *
     * @return bool true means the webhook was already processed.
     */
    protected function isWebhookProcessed(WP_REST_Request $request, WC_Order $order): bool
    {
        $noticeId = (string) $request->get_param('notificationId');
        return $this->orderWebhookFinder->hasRecord($order, $noticeId);
    }
    /**
     * Add the processed webhook to the array in meta '_payoneer_webhooks_received'
     *
     * @param WP_REST_Request $request Request to get data from.
     * @param WC_Order $refund
     *
     * @return void
     */
    protected function saveOrderWebhookProcessedMeta(WP_REST_Request $request, WC_Order $order): void
    {
        $noticeId = (string) $request->get_param('notificationId');
        $processedWebhooks = (array) $order->get_meta($this->webhooksReceivedFieldName);
        $processedWebhooks[] = $noticeId;
        $order->update_meta_data($this->webhooksReceivedFieldName, $processedWebhooks);
        $order->save();
    }
    /**
     * Check if Authorization header contains expected value.
     *
     * @param WC_Order $order Order containing expected header value.
     * @param WP_REST_Request $request Incoming request.
     *
     * @return bool Whether header value is correct.
     */
    protected function authHeaderIsCorrect(WC_Order $order, WP_REST_Request $request): bool
    {
        $expectedHeaderValue = (string) $order->get_meta($this->securityHeaderFieldName, \true);
        $actualHeaderValue = (string) $request->get_header('List-Security-Token');
        return $actualHeaderValue === $expectedHeaderValue;
    }
}
