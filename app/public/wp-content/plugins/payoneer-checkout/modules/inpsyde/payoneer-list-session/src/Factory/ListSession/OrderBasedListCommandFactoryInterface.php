<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\ListSession;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\CreateListCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\PayoneerIntegrationTypes;
interface OrderBasedListCommandFactoryInterface
{
    /**
     * @param \WC_Order $order
     * @param string $integrationType
     *
     * @psalm-param PayoneerIntegrationTypes::* $integrationType
     *
     * @return CreateListCommandInterface
     */
    public function createListCommand(\WC_Order $order, string $integrationType): CreateListCommandInterface;
}
