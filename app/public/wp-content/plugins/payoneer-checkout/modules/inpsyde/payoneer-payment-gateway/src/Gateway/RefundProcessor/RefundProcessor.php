<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\RefundProcessor;

use Exception;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Exception\ListSessionExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\PaymentContext;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\PayoutCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\PayoneerInterface;
use InvalidArgumentException;
use RuntimeException;
use WC_Order;
class RefundProcessor implements RefundProcessorInterface
{
    /**
     * @var ListSessionProvider
     */
    protected $listSessionProvider;
    /**
     * @var PaymentFactoryInterface
     */
    protected $paymentFactory;
    /**
     * @var PayoneerInterface
     */
    protected $payoneer;
    /**
     * @var string
     */
    protected $chargeIdFieldName;
    /**
     * @param PayoneerInterface $payoneer
     * @param ListSessionProvider $listSessionProvider
     * @param PaymentFactoryInterface $paymentFactory
     * @param string $chargeIdFieldName
     */
    public function __construct(PayoneerInterface $payoneer, ListSessionProvider $listSessionProvider, PaymentFactoryInterface $paymentFactory, string $chargeIdFieldName)
    {
        $this->payoneer = $payoneer;
        $this->listSessionProvider = $listSessionProvider;
        $this->paymentFactory = $paymentFactory;
        $this->chargeIdFieldName = $chargeIdFieldName;
    }
    /**
     * @inheritDoc
     */
    public function refundOrderPayment(WC_Order $order, float $amount, string $reason) : ListInterface
    {
        $payoutCommand = $this->configurePayoutCommand($order, $amount, $reason);
        try {
            return $payoutCommand->execute();
        } catch (ApiExceptionInterface $exception) {
            throw new Exception('Failed to refund order payment.', 0, $exception);
        }
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
    protected function configurePayoutCommand(WC_Order $order, float $amount, string $reason) : PayoutCommandInterface
    {
        try {
            $listSession = $this->listSessionProvider->provide(new PaymentContext($order));
        } catch (ListSessionExceptionInterface $exception) {
            throw new InvalidArgumentException('Failed to process refund: order has no associated LIST session.', 0, $exception);
        }
        $transactionId = $listSession->getIdentification()->getTransactionId();
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
}
