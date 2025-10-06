<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Network;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Network\ApplicableNetworkFactoryInterface;
class ApplicableNetworkFactory implements ApplicableNetworkFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createApplicableNetwork(string $code, string $label, string $method, string $grouping, string $registration, string $recurrence, string $operationType, array $providers, array $links, string $deferral = null): ApplicableNetworkInterface
    {
        return new ApplicableNetwork($code, $label, $method, $grouping, $registration, $recurrence, $operationType, $providers, $links, $deferral);
    }
}
