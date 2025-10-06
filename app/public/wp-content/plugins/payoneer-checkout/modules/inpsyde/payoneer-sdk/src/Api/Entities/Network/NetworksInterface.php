<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Network;

interface NetworksInterface
{
    /**
     * @return list<ApplicableNetworkInterface>
     */
    public function getApplicable(): array;
}
