<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\ChargeCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\CreateListCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\PayoutCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\UpdateListCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Style\StyleInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\System\SystemInterface;
/**
 * Represents the Payoneer API.
 */
interface PayoneerInterface
{
    /**
     * @return CreateListCommandInterface
     */
    public function getListCommand(): CreateListCommandInterface;
    /**
     * @return ChargeCommandInterface
     */
    public function getChargeCommand(): ChargeCommandInterface;
    /**
     * @return UpdateListCommandInterface
     */
    public function getUpdateCommand(): UpdateListCommandInterface;
    /**
     * @return PayoutCommandInterface
     */
    public function getPayoutCommand(): PayoutCommandInterface;
}
