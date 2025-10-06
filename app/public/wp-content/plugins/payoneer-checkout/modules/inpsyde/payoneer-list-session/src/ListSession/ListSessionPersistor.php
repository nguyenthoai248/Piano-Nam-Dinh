<?php

namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
interface ListSessionPersistor
{
    /**
     * Persist a LIST session if passed.
     * Passing null should instruct implementations to clear a previously persisted object
     *
     * @param ?ListInterface $list
     * @param ContextInterface $context
     *
     * @return bool
     * @throws \RuntimeException
     */
    public function persist(?ListInterface $list, ContextInterface $context): bool;
}
