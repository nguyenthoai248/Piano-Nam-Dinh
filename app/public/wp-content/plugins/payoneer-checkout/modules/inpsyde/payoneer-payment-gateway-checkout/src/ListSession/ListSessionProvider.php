<?php

namespace Inpsyde\PayoneerForWoocommerce\Checkout\ListSession;

use Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;

/**
 * Provides a ListSessionInterface implementation instance
 */
interface ListSessionProvider
{
    /**
     * @return ListInterface
     * @throws CheckoutExceptionInterface
     */
    public function provide(): ListInterface;
}
