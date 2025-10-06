<?php

namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\HostedPayment\PaymentProcessor;

use Exception;
use Syde\Vendor\Inpsyde\PaymentGateway\PaymentGateway;
use Syde\Vendor\Inpsyde\PaymentGateway\PaymentProcessorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\PaymentContext;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\PaymentProcessor\PayoneerCommonPaymentProcessor;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\CommandExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\InteractionExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use WC_Order;
/**
 * @psalm-import-type PaymentResult from PayoneerCommonPaymentProcessor
 */
class HostedPaymentProcessor implements PaymentProcessorInterface
{
    private PayoneerCommonPaymentProcessor $commonProcessor;
    private ListSessionProvider $sessionProvider;
    public function __construct(PayoneerCommonPaymentProcessor $commonProcessor, ListSessionProvider $sessionProvider)
    {
        $this->commonProcessor = $commonProcessor;
        $this->sessionProvider = $sessionProvider;
    }
    /**
     * @inheritDoc
     */
    public function processPayment(WC_Order $order, PaymentGateway $gateway): array
    {
        /**
         * Here we create a new List if failed to update existing one.
         *
         * This is not the nicest way how we can handle it, but this is the best we can do at the
         * moment. Ideally, we would send a new List URL to frontend so that WebSDK could replace it
         * on the go. But right now this feature is not implemented there.
         *
         * Another option would be retrieving List with GET request before the page load or AJAX
         * response sent. But this is an extra work, and an extra request, so we don't want it
         * at the moment.
         */
        try {
            $this->commonProcessor->processPayment($order, $gateway);
        } catch (InteractionExceptionInterface $exception) {
            //do nothing here
        } catch (CommandExceptionInterface $exception) {
            $exceptionWrapper = new Exception(
                /* translators: An unexpected error during the final List UPDATE before the CHARGE */
                __('Payment failed. Please attempt the payment again or contact the shop admin. This issue has been logged.', 'payoneer-checkout'),
                $exception->getCode(),
                $exception
            );
        } finally {
            if (isset($exception)) {
                do_action(
                    //we need this to remove the old List before trying again
                    'payoneer-checkout.payment_processing_failure',
                    ['order' => $order, 'errorMessage' => $exception->getMessage()]
                );
            }
            try {
                $list = $this->sessionProvider->provide(new PaymentContext($order));
            } catch (Exception $exception) {
                return $this->commonProcessor->handleFailedPaymentProcessing($order, $exceptionWrapper ?? $exception);
            }
        }
        //Make sure we have the latest List saved with order.
        //It may happen that parent::processPayment failed to update List, and we created a new one
        //in the `provide()` call above.
        $this->commonProcessor->updateOrderWithSessionData($order, $list);
        $redirectUrl = $this->createRedirectUrl($list);
        /* translators: Order note added when processing an order in hosted flow */
        $note = __('The customer is being redirected to the hosted payment page.', 'payoneer-checkout');
        $this->commonProcessor->putOrderOnHold($order, $note);
        return ['result' => 'success', 'redirect' => $redirectUrl];
    }
    /**
     * If the LIST response contains a redirect object, craft a compatible URL
     * out of the given URL and its parameters. If none is found, use our own return URL
     * as a fallback
     *
     * @param ListInterface $list
     *
     * @return string
     * @throws ApiExceptionInterface
     */
    private function createRedirectUrl(ListInterface $list): string
    {
        $redirect = $list->getRedirect();
        $baseUrl = $redirect->getUrl();
        $parameters = $redirect->getParameters();
        $parameterDict = [];
        array_walk($parameters, static function (array $param) use (&$parameterDict) {
            /** @psalm-suppress MixedArrayAssignment * */
            $parameterDict[(string) $param['name']] = urlencode((string) $param['value']);
        });
        return add_query_arg($parameterDict, $baseUrl);
    }
}
