<?php

namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway;

interface PaymentRequestValidatorInterface
{
    /**
     * @param \WC_Order $wcOrder
     * @param \WC_Payment_Gateway $gateway
     * @throws \UnexpectedValueException
     *
     * @return void
     */
    public function assertIsValid(\WC_Order $wcOrder, \WC_Payment_Gateway $gateway): void;
}
