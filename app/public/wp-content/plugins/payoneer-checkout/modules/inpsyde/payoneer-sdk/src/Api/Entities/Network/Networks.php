<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Network;

class Networks implements NetworksInterface
{
    /**
     * @var list<ApplicableNetworkInterface>
     */
    protected $applicable;
    /**
     * @param list<ApplicableNetworkInterface> $applicable
     */
    public function __construct(array $applicable)
    {
        $this->applicable = $applicable;
    }
    /**
     * @return list<ApplicableNetworkInterface>
     */
    public function getApplicable(): array
    {
        return $this->applicable;
    }
}
