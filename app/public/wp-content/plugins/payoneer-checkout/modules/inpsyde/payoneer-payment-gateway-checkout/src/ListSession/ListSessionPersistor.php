<?php

namespace Inpsyde\PayoneerForWoocommerce\Checkout\ListSession;

use Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;

/**
 * Provides persistence to a ListSessionInterface object
 */
interface ListSessionPersistor
{
    /**
     * @param ListInterface $list
     *
     * @return void
     * @throws CheckoutExceptionInterface
     */
    public function persist(ListInterface $list): void;
}
