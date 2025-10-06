<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\ListSession;

use Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutException;
use Inpsyde\PayoneerForWoocommerce\Checkout\Factory\FactoryExceptionInterface;
use Inpsyde\PayoneerForWoocommerce\Checkout\Factory\ListSession\WcBasedUpdateCommandFactoryInterface;
use Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Identification\IdentificationInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use WC_Cart;
use WC_Customer;

/**
 * Decorates another Provider. If it successfully returns a List instance,
 * it will be updated with the configured Cart/Customer data via API Call.
 * This guarantees that pre-existing data correlates to the input data
 */
class UpdatingListSessionProvider implements ListSessionProvider
{
    /**
     * @var WcBasedUpdateCommandFactoryInterface
     */
    protected $wcBasedListSessionFactory;
    /**
     * @var ListSessionProvider
     */
    protected $base;
    /**
     * @var WC_Customer
     */
    protected $wcCustomer;
    /**
     * @var WC_Cart
     */
    protected $cart;
    /**
     * @var string
     */
    protected $listSecurityToken;

    public function __construct(
        WcBasedUpdateCommandFactoryInterface $wcBasedListSessionFactory,
        ListSessionProvider $base,
        WC_Customer $wcCustomer,
        WC_Cart $cart,
        string $listSecurityToken
    ) {

        $this->wcBasedListSessionFactory = $wcBasedListSessionFactory;
        $this->base = $base;
        $this->wcCustomer = $wcCustomer;
        $this->cart = $cart;
        $this->listSecurityToken = $listSecurityToken;
    }

    public function provide(): ListInterface
    {
        /**
         * The CheckoutException from base provider will bubble up.
         * This allows other providers to continue in case we are only
         * part of a larger composite provider.
         *
         * @see CascadingListSessionManager
         */
        try {
            $baseList = $this->base->provide()->getIdentification();

            return $this->updateExistingListSession(
                $baseList,
                $this->wcCustomer,
                $this->cart,
                $this->listSecurityToken
            );
        } catch (ApiExceptionInterface | FactoryExceptionInterface $exception) {
            do_action(
                'payoneer_for_woocommerce.update_list_session_failed',
                ['exception' => $exception]
            );
            throw new CheckoutException('Could not update LIST session', 0, $exception);
        }
    }

    /**
     * Update an existing LIST session.
     *
     * @param IdentificationInterface $listSessionIdentification Existing session IDs.
     * @param WC_Customer $wcCustomer Customer to update session with.
     * @param WC_Cart $cart Cart to update session products and amounts.
     *
     * @return ListInterface Updated session instance.
     *
     * @throws ApiExceptionInterface If failed to update session.
     * @throws FactoryExceptionInterface
     */
    protected function updateExistingListSession(
        IdentificationInterface $listSessionIdentification,
        WC_Customer $wcCustomer,
        WC_Cart $cart,
        string $listSecurityToken
    ): ListInterface {

        $command = $this->wcBasedListSessionFactory->createUpdateCommand(
            $listSessionIdentification,
            $wcCustomer,
            $cart,
            $listSecurityToken
        );

        do_action('payoneer-checkout.before_update_list', [
            'longId' => $listSessionIdentification->getLongId(),
        ]);

        $list = $command->execute();

        do_action('payoneer-checkout.list_session_updated', [
            'longId' => $list->getIdentification()->getLongId(),
            'list' => $list,
        ]);

        return $list;
    }
}
