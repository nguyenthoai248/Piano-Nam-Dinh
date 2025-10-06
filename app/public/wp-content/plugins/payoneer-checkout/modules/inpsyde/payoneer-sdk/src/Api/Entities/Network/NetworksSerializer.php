<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Network;

class NetworksSerializer implements NetworksSerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serializeNetworks(NetworksInterface $networks): array
    {
        $applicable = array_map(static function (ApplicableNetworkInterface $applicableNetwork): array {
            $serializedData = ['code' => $applicableNetwork->getCode(), 'label' => $applicableNetwork->getLabel(), 'method' => $applicableNetwork->getMethod(), 'grouping' => $applicableNetwork->getGrouping(), 'registration' => $applicableNetwork->getRegistration(), 'recurrence' => $applicableNetwork->getRecurrence(), 'operationType' => $applicableNetwork->getOperationType(), 'providers' => $applicableNetwork->getProviders(), 'links' => $applicableNetwork->getLinks()];
            if ($applicableNetwork->getDeferral()) {
                $serializedData['deferral'] = $applicableNetwork->getDeferral();
            }
            return $serializedData;
        }, $networks->getApplicable());
        return ['applicable' => $applicable];
    }
}
