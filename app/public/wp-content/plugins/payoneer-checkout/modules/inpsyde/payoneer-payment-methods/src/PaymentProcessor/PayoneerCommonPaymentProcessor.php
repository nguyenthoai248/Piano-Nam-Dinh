<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\PaymentProcessor;

use Syde\Vendor\Inpsyde\PaymentGateway\PaymentGateway;
use Syde\Vendor\Inpsyde\PaymentGateway\PaymentProcessorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\CommandFactory\WcOrderBasedUpdateCommandFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\Authentication\TokenGeneratorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\MisconfigurationDetector\MisconfigurationDetectorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\PaymentContext;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant\MerchantInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\CommandExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\InteractionExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\ResponseValidator\InteractionCodeFailureInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\UpdateListCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Address\AddressInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use WC_Order;
/**
 * @psalm-type PaymentResultInfo = 'success'|'failure'
 * @psalm-type PaymentResult = array{
 *     result: PaymentResultInfo,
 *     redirect?: string
 * }
 */
class PayoneerCommonPaymentProcessor implements PaymentProcessorInterface
{
    private MisconfigurationDetectorInterface $misconfigurationDetector;
    private ListSessionProvider $sessionProvider;
    private WcOrderBasedUpdateCommandFactoryInterface $updateCommandFactory;
    private TokenGeneratorInterface $tokenGenerator;
    private string $tokenKey;
    private string $transactionIdFieldName;
    private string $sessionHashKey;
    private string $transactionUrlTemplateFieldName;
    private string $merchantIdFieldName;
    private MerchantInterface $merchant;
    // Replace with proper merchant interface/class
    public function __construct(MisconfigurationDetectorInterface $misconfigurationDetector, ListSessionProvider $sessionProvider, WcOrderBasedUpdateCommandFactoryInterface $updateCommandFactory, TokenGeneratorInterface $tokenGenerator, string $tokenKey, string $transactionIdFieldName, string $sessionHashKey, string $transactionUrlTemplateFieldName, string $merchantIdFieldName, MerchantInterface $merchant)
    {
        $this->misconfigurationDetector = $misconfigurationDetector;
        $this->sessionProvider = $sessionProvider;
        $this->updateCommandFactory = $updateCommandFactory;
        $this->tokenGenerator = $tokenGenerator;
        $this->tokenKey = $tokenKey;
        $this->transactionIdFieldName = $transactionIdFieldName;
        $this->sessionHashKey = $sessionHashKey;
        $this->transactionUrlTemplateFieldName = $transactionUrlTemplateFieldName;
        $this->merchantIdFieldName = $merchantIdFieldName;
        $this->merchant = $merchant;
    }
    /**
     * @param WC_Order $order
     * @param PaymentGateway $gateway
     *
     * @return array
     *
     * @throws ApiExceptionInterface
     * @throws CheckoutExceptionInterface
     * @throws CommandExceptionInterface
     * @throws \WC_Data_Exception
     */
    public function processPayment(WC_Order $order, PaymentGateway $gateway): array
    {
        $this->addMetaDataToOrder($order);
        /**
         * Add a unique token that will provide a little extra protection against
         * request forgery during webhook processing
         */
        $order->update_meta_data($this->tokenKey, $this->tokenGenerator->generateToken());
        $list = $this->sessionProvider->provide(new PaymentContext($order));
        $this->updateOrderWithSessionData($order, $list);
        $updateCommand = $this->updateCommandFactory->createUpdateCommand($order, $list);
        do_action('payoneer-checkout.before_update_list', ['longId' => $list->getIdentification()->getLongId(), 'list' => $list]);
        // We have a requirement to log when the List country is not set or different from
        // a billing country.
        $this->validateUpdateCommandCountry($updateCommand);
        $list = $this->updateListSession($updateCommand);
        do_action('payoneer-checkout.list_session_updated', ['longId' => $list->getIdentification()->getLongId(), 'list' => $list]);
        /**
         * This is a workaround for PN-951. If transaction was started, but not finished
         * (for example, when the page with 3DS popup was reloaded), we want to have checkout
         * hash reset to trigger List update on the next try.
         *
         * This may be removed when the proper error handling will be added to the WebSDK.
         * Now WebSDK just fails to retrieve the List from the API after the page reload and
         * nothing happens.
         */
        wc()->session->set($this->sessionHashKey, null);
        return [
            'result' => 'success',
            'redirect' => '',
            /**
             * The custom attribute is recognized by our JS code as a signal that the payment is
             * not completed yet, and the "Pay" button shouldn't be unblocked.
             */
            'messages' => '<div data-payment-state="pending"></div>',
        ];
    }
    /**
     * Add meta fields to order.
     *
     * @param WC_Order $order Order to add meta fields to.
     */
    public function addMetaDataToOrder(WC_Order $order): void
    {
        /**
         * Store Merchant ID
         */
        $merchantId = $this->merchant->getId();
        $order->update_meta_data($this->merchantIdFieldName, (string) $merchantId);
        /**
         * Store transaction ID
         */
        $transactionUrlTemplate = $this->merchant->getTransactionUrlTemplate();
        $order->update_meta_data($this->transactionUrlTemplateFieldName, $transactionUrlTemplate);
        $order->save();
    }
    /**
     * @throws \WC_Data_Exception
     * @throws CheckoutExceptionInterface
     */
    public function updateOrderWithSessionData(WC_Order $order, ListInterface $list): void
    {
        $identification = $list->getIdentification();
        $transactionId = $identification->getTransactionId();
        $order->update_meta_data($this->transactionIdFieldName, $transactionId);
        $order->add_order_note(sprintf(
            /* translators: Transaction ID supplied by WooCommerce plugin */
            __('Initiating payment with transaction ID "%1$s"', 'payoneer-checkout'),
            $transactionId
        ));
        $order->set_transaction_id($identification->getLongId());
        $order->save();
    }
    /**
     * @param UpdateListCommandInterface $updateCommand
     *
     * @return ListInterface
     *
     * @throws CommandExceptionInterface If failed to update.
     */
    public function updateListSession(UpdateListCommandInterface $updateCommand): ListInterface
    {
        try {
            return $updateCommand->execute();
        } catch (CommandExceptionInterface $commandException) {
            do_action('payoneer_for_woocommerce.update_list_session_failed', ['exception' => $commandException]);
            throw $commandException;
        }
    }
    /**
     * Take actions on payment processing failed and return fields expected by WC Payment API.
     *
     * @param WC_Order $order
     * @param \Throwable|\WP_Error|string|null $error
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     *
     * @return array
     *
     * @psalm-return PaymentResult
     */
    public function handleFailedPaymentProcessing(WC_Order $order, $error = null): array
    {
        $fallback = __('The payment was not processed. Please try again.', 'payoneer-checkout');
        switch (\true) {
            case $error instanceof \Throwable:
                $error = $this->produceErrorMessageFromException($error, $fallback);
                break;
            case $error instanceof \WP_Error:
                $error = $error->get_error_message();
                break;
            case is_string($error):
                break;
            default:
                $error = $fallback;
        }
        wc_add_notice($error, 'error');
        do_action('payoneer-checkout.payment_processing_failure', ['order' => $order, 'errorMessage' => $error]);
        WC()->session->set('refresh_totals', \true);
        return ['result' => 'failure', 'redirect' => ''];
    }
    /**
     * @param \Throwable $exception
     * @param string $fallback
     *
     * @return string
     * phpcs:disable WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
     * phpcs:disable Inpsyde.CodeQuality.NestingLevel.High
     */
    public function produceErrorMessageFromException(\Throwable $exception, string $fallback): string
    {
        if ($this->misconfigurationDetector->isCausedByMisconfiguration($exception)) {
            /* translators: Used after checking for misconfigured merchant credentials, e.g. when we encounter 401/INVALID_CONFIGURATION*/
            return __('Failed to initialize payment session. Payoneer Checkout is not configured properly.', 'payoneer-checkout');
        }
        $previous = $exception;
        do {
            if ($previous instanceof InteractionCodeFailureInterface) {
                $response = $previous->getSubject();
                $body = $response->getBody();
                $body->rewind();
                $json = json_decode((string) $body, \true);
                if (!$json || !isset($json['resultInfo'])) {
                    return $fallback;
                }
                return (string) $json['resultInfo'];
            }
        } while ($previous = $previous->getPrevious());
        return $fallback;
    }
    /**
     * Inspect the exceptions to carry out appropriate actions based on the given interaction code
     *
     * @param WC_Order $order
     * @param InteractionExceptionInterface $exception
     *
     * @return array
     * @psalm-return PaymentResult
     */
    public function handleInteractionException(WC_Order $order, InteractionExceptionInterface $exception): array
    {
        do_action('payoneer-checkout.update_list_session_failed', ['exception' => $exception, 'order' => $order]);
        return $this->handleFailedPaymentProcessing($order, $exception);
    }
    public function validateUpdateCommandCountry(UpdateListCommandInterface $updateListCommand): void
    {
        $listCountry = $updateListCommand->getCountry();
        $customer = $updateListCommand->getCustomer();
        try {
            if ($listCountry && $customer instanceof CustomerInterface) {
                $billingAddress = $customer->getAddresses()['billing'] ?? null;
                if ($billingAddress instanceof AddressInterface) {
                    $countryValid = $listCountry === $billingAddress->getCountry();
                }
            }
        } catch (ApiExceptionInterface $exception) {
            //do nothing here.
        }
        if (!isset($countryValid) || !$countryValid) {
            do_action('payoneer_checkout.invalid_country_after_final_update', ['country' => $listCountry, 'longId' => $updateListCommand->getLongId(), 'customer' => $customer]);
        }
    }
    /**
     * This method's body is not a part of the `process_payment()` method mostly because we want
     * to provide different notes from Embedded and Hosted payment processors.
     *
     * The idea of changing order status to `On Hold` was here for a long time. We tried other
     * approaches, but end up with this because we need to prevent the order from being paid with
     * other payment methods while our payment processing was started. This mostly applies
     * to the Afterpay payment method and all payment methods in Hosted mode. In all these cases
     * customer has hosted payment page opened in another tab and nothing prevents them from
     *  returning to the checkout and doing payment with another method while our payment processing
     *  was started already.
     */
    public function putOrderOnHold(WC_Order $order, string $note): void
    {
        $order->update_status('on-hold', $note);
        $order->save();
    }
}
