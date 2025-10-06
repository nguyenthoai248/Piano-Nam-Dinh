<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Callback;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackInterface;
use WC_Order;
interface WcOrderBasedCallbackFactoryInterface
{
    /**
     * @param WC_Order $order
     *
     * @return CallbackInterface
     */
    public function createCallback(WC_Order $order) : CallbackInterface;
}
