<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\ListCondition;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
class MatchNetworkCodeCondition implements ListConditionInterface
{
    protected string $code;
    /**
     * @param string $code
     */
    public function __construct(string $code)
    {
        $this->code = $code;
    }
    public function valid(ListInterface $list): bool
    {
        try {
            foreach ($list->getNetworks()->getApplicable() as $network) {
                if ($network->getCode() === $this->code) {
                    return \true;
                }
            }
        } catch (ApiExceptionInterface $ex) {
            //No networks in list session
        }
        return \false;
    }
}
