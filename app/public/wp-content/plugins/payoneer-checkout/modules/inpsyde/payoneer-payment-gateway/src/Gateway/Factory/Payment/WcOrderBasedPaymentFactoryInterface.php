<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Payment;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentInterface;
use WC_Order;
interface WcOrderBasedPaymentFactoryInterface
{
    /**
     * Create a new Payment instance from WC order.
     *
     * @param WC_Order $order WC Order to get data from.
     *
     * @return PaymentInterface Created payment object.
     */
    public function createPayment(WC_Order $order) : PaymentInterface;
}
