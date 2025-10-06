<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\ListSession;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\FactoryExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\PayoneerIntegrationTypes;
use WC_Order;
interface OrderBasedListSessionFactoryInterface
{
    /**
     * Create a new List session from given order.
     *
     * @param WC_Order $order Order to get data from
     * @param string $integrationType
     * @param string|null $hostedVersion
     * @psalm-param PayoneerIntegrationTypes::* $integrationType
     *
     * @return ListInterface
     * @throws FactoryExceptionInterface
     */
    public function createList(WC_Order $order, string $integrationType, string $hostedVersion = null): ListInterface;
}
