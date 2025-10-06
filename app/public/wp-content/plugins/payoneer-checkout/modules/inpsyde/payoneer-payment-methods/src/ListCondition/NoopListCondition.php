<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\ListCondition;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
class NoopListCondition implements ListConditionInterface
{
    public function valid(ListInterface $list): bool
    {
        return \true;
    }
}
