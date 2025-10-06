<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\ListCondition;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
class MatchNetworkGroupingCondition implements ListConditionInterface
{
    protected string $grouping;
    /**
     * @param string $grouping
     */
    public function __construct(string $grouping)
    {
        $this->grouping = $grouping;
    }
    public function valid(ListInterface $list): bool
    {
        try {
            foreach ($list->getNetworks()->getApplicable() as $network) {
                if ($network->getGrouping() === $this->grouping) {
                    return \true;
                }
            }
        } catch (ApiExceptionInterface $ex) {
            //No networks in list session
        }
        return \false;
    }
}
