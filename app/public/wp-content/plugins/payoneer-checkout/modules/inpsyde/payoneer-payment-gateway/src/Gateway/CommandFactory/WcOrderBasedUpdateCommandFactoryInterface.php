<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\CommandFactory;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\UpdateListCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use WC_Order;
interface WcOrderBasedUpdateCommandFactoryInterface
{
    /**
     * Create a configured command instance using data from provided order.
     *
     * @param WC_Order $order Order to take data from.
     *
     * @return UpdateListCommandInterface Created and configured command instance.
     *
     * @throws CheckoutExceptionInterface|ApiExceptionInterface
     */
    public function createUpdateCommand(WC_Order $order, ListInterface $list) : UpdateListCommandInterface;
}
