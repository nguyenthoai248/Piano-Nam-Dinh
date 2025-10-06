<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\Factory\ListSession;

use Inpsyde\PayoneerSdk\Api\Command\CreateListCommandInterface;

interface OrderBasedListCommandFactoryInterface
{
    public function createListCommand(\WC_Order $order): CreateListCommandInterface;
}
