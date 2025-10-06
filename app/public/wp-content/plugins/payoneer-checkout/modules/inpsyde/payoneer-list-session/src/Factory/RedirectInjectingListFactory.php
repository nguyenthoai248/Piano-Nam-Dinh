<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiException;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Identification\IdentificationInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Network\NetworksInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Redirect\RedirectInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Status\StatusInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Style\StyleInterface;
class RedirectInjectingListFactory implements ListFactoryInterface
{
    /**
     * @var ListFactoryInterface
     */
    protected $listFactory;
    public function __construct(ListFactoryInterface $listFactory)
    {
        $this->listFactory = $listFactory;
    }
    /**
     * @inheritDoc
     */
    public function createList(array $links, IdentificationInterface $identification, StatusInterface $status, PaymentInterface $payment = null, CustomerInterface $customer = null, StyleInterface $style = null, RedirectInterface $redirect = null, string $division = null, array $products = null, NetworksInterface $networks = null): ListInterface
    {
        $redirect = $redirect ?? apply_filters('payoneer-checkout.fallback_redirect', $redirect, $identification);
        if (!$redirect instanceof RedirectInterface && $redirect !== null) {
            throw new ApiException('Redirect must be instance of RedirectInterface or null');
        }
        return $this->listFactory->createList($links, $identification, $status, $payment, $customer, $style, $redirect, $division, $products, $networks);
    }
}
