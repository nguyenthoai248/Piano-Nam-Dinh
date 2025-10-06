<?php

namespace Inpsyde\PayoneerForWoocommerce\Checkout\ListSession;

use Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutExceptionInterface;

/**
 * Removes a ListSession instance
 * It is intended to purge data stored by a ListSessionPersistor
 *
 * @see ListSessionPersistor
 */
interface ListSessionRemover
{
    /**
     * Clears the storage that held a ListSession object
     * @throws CheckoutExceptionInterface
     * @return void
     */
    public function clear(): void;
}
