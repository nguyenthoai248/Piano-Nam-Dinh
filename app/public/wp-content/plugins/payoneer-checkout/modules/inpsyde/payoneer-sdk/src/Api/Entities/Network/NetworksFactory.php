<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Network;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Network\NetworksFactoryInterface;
class NetworksFactory implements NetworksFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createNetworks(array $applicable): Networks
    {
        return new Networks($applicable);
    }
}
