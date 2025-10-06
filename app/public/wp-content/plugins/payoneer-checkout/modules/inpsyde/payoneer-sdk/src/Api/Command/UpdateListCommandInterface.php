<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerInterface;
interface UpdateListCommandInterface extends ListCommandInterface, ListAwareCommandInterface
{
}
