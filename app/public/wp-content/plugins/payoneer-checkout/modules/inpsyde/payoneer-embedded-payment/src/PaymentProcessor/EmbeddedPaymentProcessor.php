<?php

namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\PaymentProcessor;

use Exception;
use Syde\Vendor\Inpsyde\PaymentGateway\PaymentGateway;
use Syde\Vendor\Inpsyde\PaymentGateway\PaymentProcessorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\PaymentProcessor\PayoneerCommonPaymentProcessor;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\CommandExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\InteractionExceptionInterface;
use WC_Order;
/**
 * @psalm-import-type PaymentResult from PayoneerCommonPaymentProcessor
 */
class EmbeddedPaymentProcessor implements PaymentProcessorInterface
{
    private PayoneerCommonPaymentProcessor $commonProcessor;
    private string $hostedModeOverrideFlag;
    private bool $isRestRequest;
    public function __construct(PayoneerCommonPaymentProcessor $commonProcessor, string $hostedModeOverrideFlag, bool $isRestRequest)
    {
        $this->commonProcessor = $commonProcessor;
        $this->hostedModeOverrideFlag = $hostedModeOverrideFlag;
        $this->isRestRequest = $isRestRequest;
    }
    public function processPayment(WC_Order $order, PaymentGateway $gateway): array
    {
        try {
            $result = $this->commonProcessor->processPayment($order, $gateway);
        } catch (InteractionExceptionInterface $exception) {
            return $this->commonProcessor->handleInteractionException($order, $exception);
        } catch (CommandExceptionInterface $exception) {
            $exceptionWrapper = new Exception(
                /* translators: An unexpected error during the final List UPDATE before the CHARGE */
                __('Payment failed. Please attempt the payment again or contact the shop admin. This issue has been logged.', 'payoneer-checkout'),
                $exception->getCode(),
                $exception
            );
            return $this->commonProcessor->handleFailedPaymentProcessing($order, $exceptionWrapper);
        }
        /**
         * We always signal success: The actual payment is supposed to be handled by the JS WebSDK
         * or by the hosted payment page.
         * in the customer's browser session. Our 'redirect' URL is only a fallback in case our JS
         * is somehow broken. For this reason, we also add the flag to force hosted mode.
         * The WebSDK is taking care of redirecting to 'thank-you' after finishing the transaction.
         * If this somehow does not happen, we still instruct WC to move to the payment page
         *
         * But this doesn't work properly for block checkout.
         */
        if (!$this->isRestRequest) {
            $result['redirect'] = add_query_arg([$this->hostedModeOverrideFlag => \true], $order->get_checkout_payment_url());
        }
        /* translators: Order note added when processing an order in embedded flow */
        $note = __('Backend processing finished, frontend processing is about to start.', 'payoneer-checkout');
        $this->commonProcessor->putOrderOnHold($order, $note . \PHP_EOL);
        return $result;
    }
}
