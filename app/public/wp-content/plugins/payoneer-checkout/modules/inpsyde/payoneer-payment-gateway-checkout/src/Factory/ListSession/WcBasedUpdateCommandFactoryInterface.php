<?php

namespace Inpsyde\PayoneerForWoocommerce\Checkout\Factory\ListSession;

use Inpsyde\PayoneerForWoocommerce\Checkout\Factory\FactoryExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Command\UpdateListCommandInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Identification\IdentificationInterface;
use WC_Cart;
use WC_Customer;

interface WcBasedUpdateCommandFactoryInterface
{
    /**
     * @throws FactoryExceptionInterface
     */
    public function createUpdateCommand(
        IdentificationInterface $listSessionIdentification,
        WC_Customer $wcCustomer,
        WC_Cart $cart,
        string $listSecurityToken
    ): UpdateListCommandInterface;
}
