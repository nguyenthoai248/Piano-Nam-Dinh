<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods;

use Exception;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\RefundContext;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Service\RefundHandlerResult;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Service\RefundOrchestratorInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\PayoutCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\PayoneerInterface;
use Syde\Vendor\Inpsyde\PaymentGateway\RefundProcessorInterface;
use InvalidArgumentException;
use RuntimeException;
use WC_Order;
use WC_Order_Refund;
class RefundProcessor implements RefundProcessorInterface
{
    private ?RefundHandlerResult $refundResult = null;
    private ?RefundContext $refundContext = null;
    private ?WC_Order_Refund $currentRefund = null;
    protected string $transactionIdFieldName;
    protected PaymentFactoryInterface $paymentFactory;
    protected PayoneerInterface $payoneer;
    protected string $chargeIdFieldName;
    protected bool $isAjax;
    protected RefundOrchestratorInterface $refundOrchestrator;
    /**
     * @param PayoneerInterface $payoneer
     * @param string $transactionIdFieldName
     * @param PaymentFactoryInterface $paymentFactory
     * @param string $chargeIdFieldName
     * @param bool $isAjax
     * @param RefundOrchestratorInterface $refundOrchestrator
     */
    public function __construct(PayoneerInterface $payoneer, string $transactionIdFieldName, PaymentFactoryInterface $paymentFactory, string $chargeIdFieldName, bool $isAjax, RefundOrchestratorInterface $refundOrchestrator)
    {
        $this->payoneer = $payoneer;
        $this->transactionIdFieldName = $transactionIdFieldName;
        $this->paymentFactory = $paymentFactory;
        $this->chargeIdFieldName = $chargeIdFieldName;
        $this->isAjax = $isAjax;
        $this->refundOrchestrator = $refundOrchestrator;
    }
    /**
     * Action handler for `woocommerce_create_refund` which is fired by the `wc_create_refund()`
     * WooCommerce core API _before_ processing or saving the refund.
     */
    public function attemptEarlyRefund(WC_Order_Refund $wcRefund, array $args = []): void
    {
        if (empty($args['refund_payment'])) {
            return;
        }
        $orderId = $wcRefund->get_parent_id();
        $wcOrder = wc_get_order($orderId);
        if (!$wcOrder instanceof WC_Order) {
            return;
        }
        $context = $this->prepareRefundContext($wcOrder, (float) $wcRefund->get_amount(), $wcRefund->get_reason());
        $result = $this->processRefundContextOnce($context, $wcOrder, $wcRefund);
        if ($result->failed()) {
            throw new Exception('Failed to refund order payment.', 0);
        }
        if ($result->waitingForWebhook()) {
            $this->terminateAsyncRequest();
        }
        // Cache the refund object, as it's needed in refundOrderProcessor().
        $this->currentRefund = $wcRefund;
    }
    /**
     * @inheritDoc
     */
    public function refundOrderPayment(WC_Order $order, float $amount, string $reason): void
    {
        $context = $this->prepareRefundContext($order, $amount, $reason);
        $result = $this->processRefundContextOnce($context, $order, $this->currentRefund);
        $this->clearRefundCaches();
        /**
         * In most cases, we bail here: The `attemptEarlyRefund()` method already captured the
         * refund object, and we processed it by now.
         * The "missingRefundData" flag is only set, when a refund is created directly via
         * `wc_refund_payment()` instead of using `wc_create_refund()`.
         */
        if (!$result->missingRefundData()) {
            return;
        }
        add_action('woocommerce_after_order_refund_object_save', function (WC_Order_Refund $refund) use ($context, $order): void {
            // Call the state-less method, which does not use or set any cached values.
            $this->processRefundContext($context, $order, $refund);
        });
    }
    /**
     * @param WC_Order $order
     * @param float $amount
     * @param string $reason
     *
     * @return PayoutCommandInterface
     *
     * @throws InvalidArgumentException If provided order has no associated LIST session.
     * @throws RuntimeException
     */
    protected function configurePayoutCommand(WC_Order $order, float $amount, string $reason): PayoutCommandInterface
    {
        $transactionId = (string) $order->get_meta($this->transactionIdFieldName, \true);
        try {
            $payment = $this->paymentFactory->createPayment($reason, $amount, 0, $amount, $order->get_currency(), $order->get_order_number());
        } catch (ApiExceptionInterface $exception) {
            throw new RuntimeException('Failed to process refund.', 0, $exception);
        }
        $chargeId = $order->get_meta($this->chargeIdFieldName, \true);
        if (!$chargeId) {
            throw new InvalidArgumentException('Failed to process refund: order has no associated charge ID');
        }
        $payoutCommand = $this->payoneer->getPayoutCommand();
        return $payoutCommand->withLongId((string) $chargeId)->withTransactionId($transactionId)->withPayment($payment);
    }
    /**
     * Reset the internal refund processing cache, for the unlikely case that a second
     * refund is processed in the same request.
     *
     * This resets the return values of the two methods `prepareRefundContext()` and
     * `processRefundContextOnce()`.
     */
    private function clearRefundCaches(): void
    {
        $this->currentRefund = null;
        $this->refundContext = null;
        $this->refundResult = null;
    }
    /**
     * Makes the payout API call once per request and caches the result.
     */
    private function prepareRefundContext(WC_Order $wcOrder, float $amount, string $reason): RefundContext
    {
        if ($this->refundContext) {
            return $this->refundContext;
        }
        $result = $this->refundOrchestrator->preparePayoutRequest($wcOrder);
        if (!$result->handled()) {
            throw new RuntimeException($result->statusMessage());
        }
        // API requires non-empty reason
        $reason = $reason !== '' ? $reason : 'No refund reason provided.';
        $payoutCommand = $this->configurePayoutCommand($wcOrder, $amount, $reason);
        try {
            $list = $payoutCommand->execute();
            $this->refundContext = RefundContext::fromList($list);
        } catch (ApiExceptionInterface $exception) {
            throw new Exception('Failed to refund order payment.', 0, $exception);
        }
        return $this->refundContext;
    }
    /**
     * Prevents duplicate orchestrator processing when both WooCommerce methods are called.
     */
    private function processRefundContextOnce(RefundContext $context, WC_Order $wcOrder, ?WC_Order_Refund $wcRefund): RefundHandlerResult
    {
        if (!$this->refundResult) {
            $this->refundResult = $this->processRefundContext($context, $wcOrder, $wcRefund);
        }
        return $this->refundResult;
    }
    /**
     * Processes the refund through the orchestrator and logs the result.
     */
    private function processRefundContext(RefundContext $context, WC_Order $wcOrder, ?WC_Order_Refund $wcRefund): RefundHandlerResult
    {
        $result = $this->refundOrchestrator->handlePayoutResponse($context, $wcOrder, $wcRefund);
        do_action('payoneer-checkout.refund-handler.api_result', ['message' => $result->statusMessage(), 'handled' => $result->handled(), 'async' => $result->waitingForWebhook(), 'success' => $result->successful()]);
        return $result;
    }
    /**
     * Instantly terminates the request, ideally with a JSON success response.
     *
     * The JSON success response will trigger a simple page refresh in WooCommerce, without
     * displaying an alert message to the admin.
     *
     * This intercepts the processing of an async refund before an email can be sent to the
     * customer and the order status flips to "Refunded".
     */
    private function terminateAsyncRequest(): void
    {
        if ($this->isAjax) {
            wp_send_json_success();
        }
        throw new RuntimeException('The refund is still processing...');
    }
}
