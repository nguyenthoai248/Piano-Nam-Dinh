<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Network;

interface NetworksFactoryInterface
{
    /**
     * @param list<ApplicableNetworkInterface> $applicable
     *
     * @return Networks
     */
    public function createNetworks(array $applicable): Networks;
}
