<?php

namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\ListSession;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\FactoryExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\UpdateListCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Identification\IdentificationInterface;
use WC_Cart;
use WC_Customer;
interface WcBasedUpdateCommandFactoryInterface
{
    /**
     * @throws FactoryExceptionInterface
     */
    public function createUpdateCommand(IdentificationInterface $listSessionIdentification, WC_Customer $wcCustomer, WC_Cart $cart): UpdateListCommandInterface;
}
