<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment;

use Syde\Vendor\Inpsyde\PaymentGateway\PaymentRequestValidatorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\CheckoutContext;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
class ListUrlPaymentRequestValidator implements PaymentRequestValidatorInterface
{
    /**
     * @var string
     */
    protected $inputName;
    /**
     * @var ?PaymentRequestValidatorInterface
     */
    protected $validator = null;
    /**
     * @var ListSessionProvider
     */
    protected $listSessionProvider;
    public function __construct(string $inputName, ListSessionProvider $listSessionProvider, PaymentRequestValidatorInterface $validator = null)
    {
        $this->inputName = $inputName;
        $this->listSessionProvider = $listSessionProvider;
        $this->validator = $validator;
    }
    /**
     * @psalm-suppress ParamNameMismatch
     *
     * @todo Remove psalm param suppression when PaymentRequestValidatorInterface::assertIsValid
     *      second param name is changed from 'param' to gateway.
     */
    public function assertIsValid(\WC_Order $order, \WC_Payment_Gateway $gateway): void
    {
        $postedListUrl = filter_input(\INPUT_POST, $this->inputName, \FILTER_SANITIZE_URL);
        $currentListUrl = $this->listSessionProvider->provide(new CheckoutContext())->getLinks()['self'];
        if ($postedListUrl !== $currentListUrl) {
            throw new \UnexpectedValueException(
                /* translators: This implies that we have a bug in the code. Merchant/Customer cannot fix it and should ideally never see it */
                __('It seems your payment has expired. Please try again', 'payoneer-checkout')
            );
        }
        $this->validator && $this->validator->assertIsValid($order, $gateway);
    }
}
