<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment;

use Syde\Vendor\Inpsyde\PaymentGateway\PaymentGateway;
use Syde\Vendor\Inpsyde\PaymentGateway\PaymentRequestValidatorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\RequestHeaderUtil;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\PaymentContext;
/**
 * Validates payment requests by checking the presence and value of a custom header.
 *
 * This class implements the PaymentRequestValidatorInterface and is responsible for ensuring that
 * incoming HTTP requests contain the necessary 'x-payoneer-long-id' header with a valid value.
 * If the header is missing or its value does not match the expected long ID, an exception is thrown,
 * indicating an error during order validation.
 */
class ListLongIdPaymentRequestValidator implements PaymentRequestValidatorInterface
{
    /**
     * @var ?PaymentRequestValidatorInterface
     */
    protected $validator = null;
    /**
     * @var ListSessionProvider
     */
    protected $listSessionProvider;
    public function __construct(ListSessionProvider $listSessionProvider, PaymentRequestValidatorInterface $validator = null)
    {
        $this->listSessionProvider = $listSessionProvider;
        $this->validator = $validator;
    }
    /**
     * Validates the payment request by checking the 'x-payoneer-long-id' header.
     *
     * This method retrieves the headers from the incoming HTTP request and checks for the presence
     * of the 'x-payoneer-long-id' header. If the header is missing, an action hook is triggered,
     * and an exception is thrown. The header value is then sanitized and compared against the expected
     * long ID from the session provider. If they do not match, another exception is thrown.
     *
     * @param \WC_Order $order The WooCommerce order being validated.
     * @param PaymentGateway $gateway The payment gateway associated with the order.
     */
    public function assertIsValid(\WC_Order $order, PaymentGateway $gateway): void
    {
        /**
         * Our frontend JS decorates fetch() to pass a custom header to all outgoing HTTP calls
         * TODO: Put this in a const/enum or make it a constructor argument
         */
        $longIdHeader = 'x-payoneer-long-id';
        $headerUtil = new RequestHeaderUtil();
        if (!$headerUtil->hasHeader($longIdHeader)) {
            /**
             * If the header is not there, something went horribly wrong and we reject the order
             */
            do_action('payoneer-checkout.missing-headers-for-validation', ['headers' => $headerUtil->getHeaders()]);
            throw new \UnexpectedValueException(
                /* translators: This means that we have not received a custom HTTP header for validation, indicating broken frontend JS */
                __('Unexpected error during order validation', 'payoneer-checkout')
            );
        }
        $headerValue = $headerUtil->getHeader($longIdHeader);
        $currentLongId = $this->listSessionProvider->provide(new PaymentContext())->getIdentification()->getLongId();
        if ($headerValue !== $currentLongId) {
            throw new \UnexpectedValueException(
                /* translators: This implies that we have a bug in the code. Merchant/Customer cannot fix it and should ideally never see it */
                __('It seems your payment has expired. Please try again', 'payoneer-checkout')
            );
        }
        $this->validator && $this->validator->assertIsValid($order, $gateway);
    }
}
