<?php

namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
interface ListSessionProviderMiddleware extends ListSessionMiddleware
{
    /**
     * @param ContextInterface $context
     * @param ListSessionProvider $next
     *
     * @return ListInterface
     * @throws \RuntimeException
     */
    public function provide(ContextInterface $context, ListSessionProvider $next): ListInterface;
}
