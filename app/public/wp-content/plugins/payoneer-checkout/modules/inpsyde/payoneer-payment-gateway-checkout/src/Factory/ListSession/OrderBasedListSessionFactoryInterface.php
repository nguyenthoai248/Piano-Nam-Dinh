<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\Factory\ListSession;

use Inpsyde\PayoneerForWoocommerce\Checkout\Factory\FactoryExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use WC_Order;

interface OrderBasedListSessionFactoryInterface
{
    /**
     * Create a new List session from given order.
     *
     * @param WC_Order $order Order to get data from
     * @param string $listSecurityToken Security token to be used for the Lu
     *
     * @return ListInterface
     * @throws FactoryExceptionInterface
     */
    public function createList(
        WC_Order $order,
        string $listSecurityToken
    ): ListInterface;
}
