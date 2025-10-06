<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Network;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiException;
class NetworksDeserializer implements NetworksDeserializerInterface
{
    /**
     * @var NetworksFactory
     */
    protected $networksFactory;
    /**
     * @var ApplicableNetworkFactory
     */
    protected $applicableNetworkFactory;
    /**
     * @param NetworksFactory $networksFactory
     * @param ApplicableNetworkFactory $applicableNetworkFactory
     */
    public function __construct(NetworksFactory $networksFactory, ApplicableNetworkFactory $applicableNetworkFactory)
    {
        $this->networksFactory = $networksFactory;
        $this->applicableNetworkFactory = $applicableNetworkFactory;
    }
    /**
     * @inheritDoc
     */
    public function deserializeNetworks(array $networksData): NetworksInterface
    {
        $applicableNetworks = array_map(function (array $applicableNetwork): ApplicableNetworkInterface {
            $this->assertArrayHasKeys(['code', 'label', 'method', 'grouping', 'registration', 'recurrence', 'operationType', 'links'], $applicableNetwork);
            return $this->applicableNetworkFactory->createApplicableNetwork($applicableNetwork['code'], $applicableNetwork['label'], $applicableNetwork['method'], $applicableNetwork['grouping'], $applicableNetwork['registration'], $applicableNetwork['recurrence'], $applicableNetwork['operationType'], $applicableNetwork['providers'] ?? [], $applicableNetwork['links'], $applicableNetwork['deferral'] ?? null);
        }, $networksData['applicable'] ?? []);
        return $this->networksFactory->createNetworks($applicableNetworks);
    }
    /**
     * @throws ApiException
     */
    protected function assertArrayHasKeys(array $keys, array $array): void
    {
        foreach ($keys as $key) {
            if (!\array_key_exists($key, $array)) {
                throw new ApiException("Data contains no expected `{$key}` element.");
            }
        }
    }
}
