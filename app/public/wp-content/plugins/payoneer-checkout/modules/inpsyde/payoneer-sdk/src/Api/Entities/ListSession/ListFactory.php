<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Identification\IdentificationInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Network\NetworksInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Redirect\RedirectInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Status\StatusInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Style\StyleInterface;
class ListFactory implements ListFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createList(array $links, IdentificationInterface $identification, StatusInterface $status, PaymentInterface $payment = null, CustomerInterface $customer = null, StyleInterface $style = null, RedirectInterface $redirect = null, string $division = null, array $products = null, NetworksInterface $networks = null): ListInterface
    {
        return new ListSession($links, $identification, $status, $payment, $customer, $style, $redirect, $division, $products, $networks);
    }
}
