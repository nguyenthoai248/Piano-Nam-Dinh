<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Identification\IdentificationInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Network\NetworksInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Redirect\RedirectInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Status\StatusInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Style\StyleInterface;
/**
 * A service able to create ListInterface instance.
 */
interface ListFactoryInterface
{
    /**
     * Create a new List object.
     *
     * @param array{self: string, lang?: string, customer?: string} $links Links related to
     *                                                                     the current session.
     * @param IdentificationInterface $identification An object with payment session identifiers.
     * @param StatusInterface $status An object with LIST session status data.
     * @param PaymentInterface|null $payment An object with payment-related data.
     * @param CustomerInterface|null $customer An object with customer data.
     * @param StyleInterface|null $style
     * @param RedirectInterface|null $redirect
     * @param string|null $division
     * @param ProductInterface[]|null $products
     *
     * @return ListInterface An object representing payment session.
     *
     * @throws ApiExceptionInterface If something went wrong.
     */
    public function createList(array $links, IdentificationInterface $identification, StatusInterface $status, PaymentInterface $payment = null, CustomerInterface $customer = null, StyleInterface $style = null, RedirectInterface $redirect = null, string $division = null, array $products = null, NetworksInterface $networks = null): ListInterface;
}
